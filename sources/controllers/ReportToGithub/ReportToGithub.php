<?php

/**
* @package manifest file for Report to github
* @version 1.0
* @author Siddhartha Gupta (https://github.com/siddhartha-gupta)
* @copyright Copyright (c) 2014, Siddhartha Gupta
* @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

/*
* Version: MPL 1.1
*
* The contents of this file are subject to the Mozilla Public License Version
* 1.1 (the "License"); you may not use this file except in compliance with
* the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
*
* Software distributed under the License is distributed on an "AS IS" basis,
* WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
* for the specific language governing rights and limitations under the
* License.
*
* The Initial Developer of the Original Code is
* Siddhartha Gupta (https://github.com/siddhartha-gupta)
* Portions created by the Initial Developer are Copyright (C) 2012
* the Initial Developer. All Rights Reserved.
*
* Contributor(s): Big thanks to all contributor(s)
*
*/

if (!defined('ELK'))
	die('Hacking attempt...');

class ReportToGithub {
	protected static $instance;
	public static $sourceFolder = '/ReportToGithub/';

	/**
	 * Singleton method
	 *
	 * @return ReportToGithub
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new ReportToGithub();

			loadLanguage('ReportToGithub');
		}
		return self::$instance;
	}

	public function __construct() {}

	/**
	 * @param string $className
	 */
	public static function loadClass($className) {
		switch($className) {

			default:
				break;
		}
	}

	public static function includeAssets() {
		global $context, $settings, $txt;

		$context['insert_after_template'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			function loadLPScript() {
				var js = document.createElement("script");
				js.type = "text/javascript";
				js.src = "' . $settings['default_theme_url'] . '/scripts/ReportToGithub/ReportToGithub.min.js";
				js.onload = function() {}
				document.body.appendChild(js);
			}
		// ]]></script>';

		self::checkJsonEncodeDecode();
	}

	public static function addAdminPanel(&$admin_areas) {
		global $txt;

		$admin_areas['config']['areas']['reporttogithub'] = array(
			'label' => $txt['rtg_menu'],
			'file' => 'ReportToGithub.controller.php',
			'controller' => 'ReportToGithubAdmin_Controller',
			'function' => 'action_index',
			'icon' => 'transparent.png',
			'class' => 'admin_img_packages',
			'permission' => array('admin_forum'),
			'subsections' => array(
				'generalsettings' => array($txt['rtg_general_settings'], 'admin_forum'),
				'recountstats' => array($txt['rtg_recount_stats'], 'admin_forum'),
			),
		);
	}

	private static function checkJsonEncodeDecode() {
		global $sourcedir;

		if (!function_exists('json_decode')) {
			require_once (SOURCEDIR . '/controllers/ReportToGithub/JSON.php');

			function json_decode($content, $assoc = false) {
				if ($assoc) {
					$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
				} else {
					$json = new Services_JSON;
				}
				return $json->decode($content);
			}
		}

		if (!function_exists('json_encode')) {
			require_once (SOURCEDIR . '/controllers/ReportToGithub/JSON.php');

			function json_encode($content) {
				$json = new Services_JSON;
				return $json->encode($content);
			}
		}
	}
}
ReportToGithub::getInstance();

?>
