<?php
$requestUri = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');

if (stripos($requestUri, '/_rest/') !== FALSE) {
	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

	/** @var \ArnoSchoon\ExtbaseRest\Router $router */
	$router = $objectManager->get('ArnoSchoon\\ExtbaseRest\\Router');

	echo $router->dispatch($requestUri);
} else {
	header(\TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_400);
}