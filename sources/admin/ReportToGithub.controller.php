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

		$subActions = array(
			'generalsettings' => array($this, 'action_generalSettings'),
			'savegeneralsettings' => array($this, 'action_saveGeneralSettings'),
			'optimizetables' => array($this, 'action_optimizetables'),
		);

		// Set up action/subaction stuff.
		$action = new Action('reporttogithub');

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
		// Work out exactly who it is we are calling. call integrate_sa_packages
		$subAction = $action->initialize($subActions, 'generalsettings');

		// Set up for the template
		$context['sub_action'] = $subAction;

		// Lets just do it!
		$action->dispatch($subAction);
	}

	public function action_generalSettings($return_config = false) {
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

	public function action_saveGeneralSettings() {
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

	public function action_optimizetables() {
		isAllowedTo('admin_forum');

		// Lets fire the bullet.
		@set_time_limit(300);
		/*$this->dbInstance->optimizeLikes();

		$resp = array('result' => true);
		return ReportToGithub::$ReportToGithubUtils->sendJSONResponse($resp);*/
	}
}

?>
