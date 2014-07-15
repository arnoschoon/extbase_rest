<?php
namespace ArnoSchoon\ExtbaseRest\Hook;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendRequestPreProcessor
 *
 * @package ArnoSchoon\ExtbaseRest\Hook
 */
class FrontendRequestPreProcessor {

	/**
	 * @param array $foo
	 * @param array $bar
	 *
	 * @return void
	 */
	public function mapRestRequestToEid(array $foo, array $bar) {
		$requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
		$queryArguments = GeneralUtility::_GET();

		if (is_array($queryArguments) && !array_key_exists('eID', $queryArguments) && stripos($requestUri, '/_rest/') !== FALSE) {
			GeneralUtility::_GETset('Tx_ExtbaseRest_Router', 'eID');
		}
	}
}