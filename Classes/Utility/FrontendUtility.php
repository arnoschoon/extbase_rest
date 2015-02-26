<?php
namespace ArnoSchoon\ExtbaseRest\Utility;

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

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Class FrontendUtility
 *
 * @package ArnoSchoon\ExtbaseRest\Utility
 */
class FrontendUtility {

	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected static $typoScriptFrontendBackup;

	/**
	 * @return void
	 */
	public static function startSimulation() {
		self::$typoScriptFrontendBackup = $GLOBALS['TSFE'];

		$pageId = (int) $_SERVER['HTTP_X_TYPO3_ID'];
		$languageId = (int) $_SERVER['HTTP_X_TYPO3_L'];

		if ($languageId > 0) {
			GeneralUtility::_GETset($languageId, 'L');
		}

		/** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $typoScriptFrontend */
		$typoScriptFrontend = GeneralUtility::makeInstance(
			'TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController',
			$GLOBALS['TYPO3_CONF_VARS'],
			$pageId,
			0
		);

			// export the fresh instance of TypoScriptFrontendController to globals
			// code we are about to execute relies on $GLOBALS[TSFE] being set
			// if $GLOBALS[TSFE] is NULL PHP emits the following warning
			// Creating default object from empty value
		$GLOBALS['TSFE'] = $typoScriptFrontend;

		EidUtility::initTCA();

		$typoScriptFrontend->initFEuser();
		$typoScriptFrontend->determineId();
		$typoScriptFrontend->initTemplate();
		$typoScriptFrontend->getConfigArray();
		$typoScriptFrontend->convPOSTCharset();
		$typoScriptFrontend->settingLanguage();
		$typoScriptFrontend->settingLocale();

			// force absRefPrefix, any URL generated further on should respect this setting
		if ($typoScriptFrontend->tmpl instanceof TemplateService && is_array($typoScriptFrontend->tmpl->setup)
			&& array_key_exists('config.', $typoScriptFrontend->tmpl->setup)
			&& is_array($typoScriptFrontend->tmpl->setup['config.'])
			&& array_key_exists('absRefPrefix', $typoScriptFrontend->tmpl->setup['config.'])
			) {
			$typoScriptFrontend->absRefPrefix = $typoScriptFrontend->tmpl->setup['config.']['absRefPrefix'];
		}
	}

	/**
	 * @return void
	 */
	public static function stopSimulation() {
		$GLOBALS['TSFE'] = self::$typoScriptFrontendBackup;
	}

}
?>