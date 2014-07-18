<?php
namespace ArnoSchoon\ExtbaseRest\Controller;

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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DummyController
 *
 * @package ArnoSchoon\ExtbaseRest\Controller
 */
class DummyController extends ActionController {

	/**
	 * @return string
	 *
	 * @restMethod POST
	 */
	public function indexAction() {
		return json_encode(array(
			'POST'
		));
	}

	/**
	 * @return string
	 *
	 * @restMethod GET
	 */
	public function showAction() {
		return json_encode(array(
			'GET'
		));
	}

}
?>