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
		require_once(SUBSDIR . '/ReportToGithub.subs.php');
		require_once(SUBSDIR . '/SettingsForm.class.php');

		$this->dbInstance = new ReportToGithubDB();
		loadtemplate('ReportToGithubAdmin');
		$context['page_title'] = $txt['rtg_admin_panel'];

		$subActions = array(
			'generalsettings' => array($this, 'action_generalSettings'),
			'savegeneralsettings' => array($this, 'action_saveGeneralSettings'),
			'githubsetup' => array($this, 'action_githubSetup'),
			'savegithubsetup' => array($this, 'action_saveGithubSetup'),
			'optimizetables' => array($this, 'action_optimizetables'),
		);

		// Set up action/subaction stuff.
		$action = new Action('reporttogithub');

		// Load tabs menu, text etc for the admin panel
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['rtg_admin_panel'],
			'help' => '',
			'description' => $txt['rtg_admin_panel_desc'],
		);

		// Work out exactly who it is we are calling. call integrate_sa_packages
		$subAction = $action->initialize($subActions, 'generalsettings');
		$context['sub_action'] = $subAction;

		// Lets just do it!
		$action->dispatch($subAction);
	}

	public function action_generalSettings($return_config = false) {
		global $txt, $context;

		// Load the boards list
		require_once(SUBSDIR . '/Boards.subs.php');
		$boards = getBoardList(array('override_permissions' => true, 'not_redirection' => true), true);
		$rtg_boards = array('');
		foreach ($boards as $board)
			$rtg_boards[$board['id_board']] = $board['cat_name'] . ' - ' . $board['board_name'];

		$general_settings = array(
			array('check', 'rtg_mod_enable', 'subtext' => $txt['rtg_mod_enable_desc']),
			array('select', 'rtg_active_boards', $rtg_boards)
		);

		$context['page_title'] = $txt['rtg_admin_panel'];
		$context['sub_template'] = 'rtg_admin_general_settings';
		Settings_Form::prepare_db($general_settings);
	}

	public function action_saveGeneralSettings() {
		/* I can has Adminz? */
		isAllowedTo('admin_forum');
		checkSession();

		// Load the boards list
		require_once(SUBSDIR . '/Boards.subs.php');
		$boards = getBoardList(array('override_permissions' => true, 'not_redirection' => true), true);
		$rtg_boards = array('');
		foreach ($boards as $board)
			$rtg_boards[$board['id_board']] = $board['cat_name'] . ' - ' . $board['board_name'];

		$general_settings = array(
			array('check', 'rtg_mod_enable'),
			array('select', 'rtg_active_boards', $rtg_boards),
		);

		Settings_Form::save_db($general_settings);
		redirectexit('action=admin;area=reporttogithub;sa=generalsettings');
	}

	public function action_githubsetup() {
		global $txt, $context;

		/* I can has Adminz? */
		isAllowedTo('admin_forum');

		// print_r($context['rtg_github_error']);
		if (empty($context['rtg_github_error'])) {
			$context['rtg_github_error'] = array();
		}

		if(isset($_REQUEST['save_prefrences']) && !empty($_REQUEST['save_prefrences'])) {
			$this->action_saveGithubSetup();
		} else {
			$context['report_to_github']['credentials'] = $this->dbInstance->getCredentials();
			$context['report_to_github']['general_settings'] = array(
				array(
					'type' => 'text',
					'name' => 'rtg_github_repo',
					'size' => 40,
					'subtext' => $txt['rtg_github_repo_desc'],
					'value' => $context['report_to_github']['credentials']['rtg_github_repo']
				),
				array(
					'type' => 'text',
					'name' => 'rtg_github_owner',
					'size' => 40,
					'subtext' => $txt['rtg_github_owner_desc'],
					'value' => $context['report_to_github']['credentials']['rtg_github_owner']
				),
				array(
					'type' => 'text',
					'name' => 'rtg_github_username',
					'size' => 40,
					'subtext' => $txt['rtg_github_username_desc'],
					'value' => $context['report_to_github']['credentials']['rtg_github_username']
				),
				array(
					'type' => 'password',
					'name' => 'rtg_github_password',
					'size' => 40,
					'subtext' => $txt['rtg_github_password_desc'],
					'value' => $context['report_to_github']['credentials']['rtg_github_password']
				),
			);
			$context['page_title'] = $txt['rtg_admin_panel'];
			$context['sub_template'] = 'rtg_admin_github_setup';
		}
	}

	public function action_saveGithubSetup() {
		global $context;

		isAllowedTo('admin_forum');
		checkSession();

		require_once(SUBSDIR . '/Auth.subs.php');
		loadLanguage('Errors');
		$errors = array();
		unset($_REQUEST['save_prefrences']);

		$rtgGithubRepo = $_REQUEST['rtg_github_repo'];
		$rtgGithubOwner = $_REQUEST['rtg_github_owner'];
		$rtgGithubUsername = $_REQUEST['rtg_github_username'];
		$rtgGithubPassword = $_REQUEST['rtg_github_password'];

		// if any field is empty you need to go back
		if(empty($rtgGithubRepo)) {
			$errors['rtg_github_repo'] = 'You need to fill the repository name';
		}

		if(empty($rtgGithubOwner)) {
			$errors['rtg_github_owner'] = 'You need to fill the repository owner name';
		}

		if(empty($rtgGithubUsername)) {
			$errors['rtg_github_username'] = 'You need to fill your github username';
		}

		if(empty($rtgGithubPassword)) {
			$errors['rtg_github_password'] = 'You need to fill your github password';
		}

		if (!empty($errors)) {
			foreach ($errors as $key => $error) {
				$context['rtg_github_error'][$key] = $error;
			}
			return $this->action_githubsetup();
		} else {
			$passHash = uniqid(mt_rand(), true);
			$password = $this->encrypt($rtgGithubPassword, $passHash);
			$this->dbInstance->updateCredentials(
				array($rtgGithubRepo, $rtgGithubOwner, $rtgGithubUsername, $password, $passHash)
			);
			redirectexit('action=admin;area=reporttogithub;sa=githubsetup');
		}
	}

	private function encrypt($password, $encryption_key) {
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($password), MCRYPT_MODE_ECB, $iv);
		return base64_encode($encrypted_string);
	}

	private function decrypt($encryptedPass, $encryption_key) {
		$encryptedPass = base64_decode($encryptedPass);
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encryptedPass, MCRYPT_MODE_ECB, $iv);
		return $decrypted_string;
	}

	public function action_optimizetable() {
		isAllowedTo('admin_forum');
	}
}

?>
