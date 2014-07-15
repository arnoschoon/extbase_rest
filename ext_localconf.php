<?php
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] =
	'ArnoSchoon\\ExtbaseRest\\Hook\\FrontendRequestPreProcessor->mapRestRequestToEid';

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['Tx_ExtbaseRest_Router'] =
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('extbase_rest', 'Resources/Private/PHP/Router.php');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('extbase_rest', 'setup', '
config.tx_extbase {
	mvc {
		requestHandlers {
			ArnoSchoon\ExtbaseRest\Mvc\Web\RestRequestHandler = ArnoSchoon\ExtbaseRest\Mvc\Web\RestRequestHandler
		}
	}
}
', 43);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'ArnoSchoon.ExtbaseRest',
	'Example',
	array(
		'Dummy' => 'index,show'
	)
);