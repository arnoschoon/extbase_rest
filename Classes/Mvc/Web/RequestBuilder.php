<?php
namespace ArnoSchoon\ExtbaseRest\Mvc\Web;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Arno Schoon <arno@maxserv.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use ArnoSchoon\ExtbaseRest\Router;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequestBuilder
 *
 * @package ArnoSchoon\ExtbaseRest\Mvc\Web
 */
class RequestBuilder extends \TYPO3\CMS\Extbase\Mvc\Web\RequestBuilder {

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 * @inject
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected static $reservedArgumentNames = array('controller', 'format');

	/**
	 * Builds a web request object from the raw HTTP information and the configuration
	 *
	 * @return \TYPO3\CMS\Extbase\MVC\Web\Request The web request as an object
	 *
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception
	 */
	public function build() {
		$this->loadDefaultValues();
		$parameters = $this->buildParametersFromRequest();

		/** @var \TYPO3\CMS\Extbase\MVC\Web\Request $request */
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\MVC\\Web\\Request');
		$request->setPluginName($this->pluginName);
		$request->setControllerExtensionName($this->extensionName);

		if ($this->vendorName) {
			$request->setControllerVendorName($this->vendorName);
		}

		$request->setRequestURI(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));
		$request->setBaseURI(GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));
		$request->setMethod(isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) ?
				$_SERVER['REQUEST_METHOD'] : 'GET');

		if (is_string($parameters['controller']) && array_key_exists($parameters['controller'], $this->allowedControllerActions)) {
			$controllerName = filter_var($parameters['controller'], FILTER_SANITIZE_STRING);
		} else {
			throw new \TYPO3\CMS\Extbase\Mvc\Exception(
				'You either failed to specify the controller in your request or this plugin is not allowed to execute it.
				Please check for Tx_Extbase_Utility_Extension::configurePlugin() in your ext_localconf.php.',
				1391083601
			);
		}

		$request->setControllerName($controllerName);

		foreach ($parameters as $argumentName => $argumentValue) {
			$request->setArgument($argumentName, $argumentValue);
		}

		$actionName = $this->resolveActionNameByRequestMethod($request);

		if ($actionName === NULL) {
			throw new \TYPO3\CMS\Extbase\Mvc\Exception(
				'The used HTTP request method (' . $request->getMethod() . ') is not allowed for any of the allowed actions.
				Please check the @restMethod annotations in ' . $request->getControllerObjectName(),
				1295479651
			);
		}
		$request->setControllerActionName($actionName);

		if (is_string($parameters['format']) && (strlen($parameters['format']))) {
			$request->setFormat(filter_var($parameters['format'], FILTER_SANITIZE_STRING));
		}

		return $request;
	}

	/**
	 * @return array
	 */
	protected function buildParametersFromRequest() {
		$rawRequestBody = file_get_contents('php://input');
		$requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
		$match = NULL;

		$pluginNamespace = $this->extensionService->getPluginNamespace($this->extensionName, $this->pluginName);
		$parameters = GeneralUtility::_GPmerged($pluginNamespace);

		if (preg_match(Router::CONTROLLER_FORMAT_PATTERN, $requestUri, $match) === 1) {
			$parameters['controller'] = \ArnoSchoon\ExtbaseRest\Utility\GeneralUtility::conditionalUpperCamelCase($match[1]);
			$parameters['format'] = $match[2];
		}

		if (!empty($rawRequestBody) && $parameters['format'] === 'json') {
			$arguments = json_decode($rawRequestBody, TRUE);

			if ($arguments !== NULL) {
				ArrayUtility::mergeRecursiveWithOverrule($parameters, $arguments);
			}
		}

		return $parameters;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\MVC\Web\Request $request
	 *
	 * @return string|null
	 */
	protected function resolveActionNameByRequestMethod(\TYPO3\CMS\Extbase\MVC\Web\Request $request) {
		$allowedActions = $this->allowedControllerActions[$request->getControllerName()];
		$requestMethod = $request->getMethod();
		$possibleActionNames = array();

		foreach ($allowedActions as $actionName) {
			$actionMethodTags = $this->reflectionService->getMethodTagsValues(
				$request->getControllerObjectName(),
				$actionName . 'Action'
			);

			if (
				is_array($actionMethodTags)
				&& array_key_exists('restMethod', $actionMethodTags)
				&& is_array($actionMethodTags['restMethod'])
				&& in_array($requestMethod, $actionMethodTags['restMethod'])
			) {
				$possibleActionNames[$actionName] = $actionMethodTags;
			}
		}

		if (count($possibleActionNames) > 1) {
			/**
			 * Sort the array by the number of param tags
			 */
			uasort($possibleActionNames, function(array $a, array $b) {
				$paramCountA = 0;
				$paramCountB = 0;

				if (
					array_key_exists('param', $a)
					&& is_array($a['param'])
				) {
					$paramCountA = count($a['param']);
				}

				if (
					array_key_exists('param', $b)
					&& is_array($b['param'])
				) {
					$paramCountB = count($b['param']);
				}

				if ($paramCountA === $paramCountB) {
					return 0;
				}

				return ($paramCountA > $paramCountB) ? - 1 : 1;
			});

			foreach ($possibleActionNames as $possibleActionName => $possibleActionMethodTags) {
				if (!$this->canActionSatisfyRequest($possibleActionMethodTags, $request)) {
					unset($possibleActionNames[$possibleActionName]);
				}
			}
		}

		return count($possibleActionNames) > 0 ? current(array_keys($possibleActionNames)) : NULL;
	}

	/**
	 * @var array $actionMethodTags
	 * @param \TYPO3\CMS\Extbase\MVC\Web\Request $request
	 *
	 * @return bool
	 */
	protected function canActionSatisfyRequest(array $actionMethodTags, \TYPO3\CMS\Extbase\MVC\Web\Request $request) {
		$requestArgumentNames = array_keys($request->getArguments());
		$canActionSatisfyRequest = TRUE;

		if (
			array_key_exists('param', $actionMethodTags)
			&& is_array($actionMethodTags['param'])
		) {
			$availableParams = 0;
			$methodParamAnnotation = implode('%', $actionMethodTags['param']) . '%';

			foreach ($requestArgumentNames as $requestArgumentName) {
				if (
					!in_array($requestArgumentName, self::$reservedArgumentNames)
					&& stripos($methodParamAnnotation, '$' . $requestArgumentName . '%') !== FALSE
				) {
					$availableParams++;
				}
			}

			$canActionSatisfyRequest = ($availableParams > 0 && $availableParams == count($actionMethodTags['param']));
		}

		return $canActionSatisfyRequest;
	}

}