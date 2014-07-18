<?php
namespace ArnoSchoon\ExtbaseRest;

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

use ArnoSchoon\ExtbaseRest\Utility\FrontendUtility;
use ArnoSchoon\ExtbaseRest\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Class Router
 *
 * @package ArnoSchoon\ExtbaseRest
 */
class Router {

	/**
	 * @var string
	 */
	const PLUGIN_NAMESPACE_PATTERN = '/\/_rest\/([^\/]+)\//i';

	/**
	 * @var string
	 */
	const CONTROLLER_FORMAT_PATTERN = '/\/_rest\/[^\/]+\/([^\.]+)\.([a-z0-9]+)/i';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @param string $requestUri
	 *
	 * @return string|null
	 */
	public function dispatch($requestUri) {
		$configuration = array();
		$response = NULL;
		$match = NULL;

		if (preg_match(self::PLUGIN_NAMESPACE_PATTERN, $requestUri, $match) === 1) {
			FrontendUtility::startSimulation();

			/** @var \ArnoSchoon\ExtbaseRest\Core\Bootstrap $bootstrap */
			$bootstrap = $this->objectManager->get('ArnoSchoon\\ExtbaseRest\\Core\\Bootstrap');

			$namespaceParts = explode('.', $match[1]);

			if (count($namespaceParts) == 3) {
				$configuration['vendorName'] = GeneralUtility::conditionalUpperCamelCase($namespaceParts[0]);
				$configuration['extensionName'] = GeneralUtility::conditionalUpperCamelCase($namespaceParts[1]);
				$configuration['pluginName'] = GeneralUtility::conditionalUpperCamelCase($namespaceParts[2]);
			} else {
				$configuration['extensionName'] = GeneralUtility::conditionalUpperCamelCase($namespaceParts[0]);
				$configuration['pluginName'] = GeneralUtility::conditionalUpperCamelCase($namespaceParts[1]);
			}

			$response = $bootstrap->run('', $configuration);


			FrontendUtility::stopSimulation();
		} else {
			header(HttpUtility::HTTP_STATUS_400);
		}

		return $response;
	}

}
?>