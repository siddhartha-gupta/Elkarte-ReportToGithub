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

/*function ReportToGithubAdmin_Controller() {
	
}*/

class ReportToGithubAdmin_Controller extends Action_Controller {
	private $dbInstance;

	public function __construct() {}

	public function action_index() {
		global $txt, $context;

		isAllowedTo('admin_forum');

		require_once(SUBSDIR . '/ReportToGithub/ReportToGithubAdmin.subs.php');
		require_once(SUBSDIR . '/SettingsForm.class.php');

		$this->dbInstance = new ReportToGithubAdminDB();
		loadtemplate('ReportToGithubAdmin');

		$context['page_title'] = $txt['rtg_admin_panel'];
		$defaultActionFunc = 'generalSettings';

		// Load tabs menu, text etc for the admin panel
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['rtg_admin_panel'],
			'tabs' => array(
				'generalsettings' => array(
					'label' => $txt['rtg_general_settings'],
					'url' => 'generalsettings',
				),
				'optimizetables' => array(
					'label' => $txt['rtg_recount_stats'],
					'url' => 'optimizetables',
				),
			),
		);
		$context[$context['admin_menu_name']]['tab_data']['active_button'] = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'generalsettings';

		$subActions = array(
			'generalsettings' => 'generalSettings',
			'savegeneralsettings' => 'saveGeneralSettings',
			'optimizetables' => 'optimizetables',
		);

		//wakey wakey, call the func you lazy
		if (isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) && method_exists(LikePosts::$LikePostsAdmin, $subActions[$_REQUEST['sa']]))
			return $this->$subActions[$_REQUEST['sa']]();

		// At this point we can just do our default.
		$this->$defaultActionFunc();
	}

	public function generalSettings($return_config = false) {
		global $txt, $context, $sourcedir;

		$general_settings = array(
			array('check', 'rtg_mod_enable', 'subtext' => $txt['rtg_mod_enable_desc']),
			array('check', 'rtg_active_boards', 'subtext' => $txt['rtg_active_boards_desc'])
		);

		$context['page_title'] = $txt['rtg_admin_panel'];
		$context['sub_template'] = 'rtg_admin_general_settings';
		$context['like_posts']['tab_name'] = $txt['rtg_general_settings'];
		$context['like_posts']['tab_desc'] = $txt['rtg_general_settings_desc'];
		Settings_Form::prepare_db($general_settings);
	}

	public function saveGeneralSettings() {
		global $sourcedir;

		/* I can has Adminz? */
		isAllowedTo('admin_forum');
		checkSession('request', '', true);

		$general_settings = array(
			array('check', 'rtg_mod_enable'),
			array('check', 'rtg_active_boards'),
		);

		Settings_Form::save_db($general_settings);
		redirectexit('action=admin;area=likeposts;sa=generalsettings');
	}

	public function optimizetables() {
		isAllowedTo('admin_forum');

		// Lets fire the bullet.
		@set_time_limit(300);
		/*$this->dbInstance->optimizeLikes();

		$resp = array('result' => true);
		return ReportToGithub::$ReportToGithubUtils->sendJSONResponse($resp);*/
	}
}

?>
