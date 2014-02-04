<?php
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

class Tx_ExtbaseRest_Mvc_Web_RestRequestHandler extends Tx_Extbase_MVC_Web_FrontendRequestHandler {

	/**
	 * @param Tx_ExtbaseRest_Mvc_Web_RequestBuilder $requestBuilder
	 * @return void
	 */
	public function injectRequestBuilder(Tx_ExtbaseRest_Mvc_Web_RequestBuilder $requestBuilder) {
		$this->requestBuilder = $requestBuilder;
	}

	/**
	 * Handles the web request. The response will automatically be sent to the client.
	 *
	 * @return Tx_Extbase_MVC_Web_Response
	 */
	public function handleRequest() {
		$request = $this->requestBuilder->build();

		// TODO: implement request verification (fake hmac)

		/** @var $requestHashService \TYPO3\CMS\Extbase\Security\Channel\RequestHashService */
		//$requestHashService = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Security\\Channel\\RequestHashService');
		//$requestHashService->verifyRequest($request);

		$request->setHmacVerified(TRUE);

		if ($this->extensionService->isActionCacheable(NULL, NULL, $request->getControllerName(), $request->getControllerActionName())) {
			$request->setIsCached(TRUE);
		} else {
			$contentObject = $this->configurationManager->getContentObject();
			if ($contentObject->getUserObjectType() === \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER) {
				$contentObject->convertToUserIntObject();
				// \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::convertToUserIntObject() will recreate the object, so we have to stop the request here
				return NULL;
			}
			$request->setIsCached(FALSE);
		}

		/** @var $response \TYPO3\CMS\Extbase\Mvc\Web\Response */
		$response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response');
		$this->dispatcher->dispatch($request, $response);

		if(stripos($request->getFormat(), 'json') !== FALSE) {
			$response->setHeader('Content-type', 'application/json; charset=UTF-8');
		}

		return $response;
	}

	/**
	 * This request handler can handle any web request.
	 *
	 * @return boolean If the request is a web request, TRUE otherwise FALSE
	 */
	public function canHandleRequest() {
		$requestUri = t3lib_div::getIndpEnv('REQUEST_URI');

		return (
			TYPO3_MODE === 'FE'
			&& stripos($requestUri, '/_rest/') !== FALSE
			&& preg_match(Tx_ExtbaseRest_Router::PLUGIN_NAMESPACE_PATTERN, $requestUri) === 1
		);
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * @return integer The priority of the request handler.
	 */
	public function getPriority() {
		return 200;
	}

} 