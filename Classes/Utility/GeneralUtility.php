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

/**
 * Class GeneralUtility
 *
 * @package ArnoSchoon\ExtbaseRest\Utility
 */
class GeneralUtility {

	/**
	 * @param string
	 *
	 * @return string
	 */
	public static function conditionalUpperCamelCase($subject) {
		// if $subject is lower cased and contains underscores
		if ($subject === strtolower($subject) && stripos($subject, '_') !== FALSE) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($subject);
		}

		// always upper case first character
		if ($subject[0] === strtolower($subject[0])) {
			$subject = ucfirst($subject);
		}

		return $subject;
	}

} 