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
				'recountstats' => array(
					'label' => $txt['rtg_recount_stats'],
					'url' => 'recountlikestats',
				),
			),
		);
		$context[$context['admin_menu_name']]['tab_data']['active_button'] = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'generalsettings';

		$subActions = array(
			'generalsettings' => 'generalSettings',
			'savegeneralsettings' => 'saveGeneralSettings',
			'recountlikestats' => 'recountLikeStats',
			'optimizelikes' => 'optimizeLikes',
			'removeduplikes' => 'removeDupLikes',
			'recountlikestotal' => 'recountLikesTotal',
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
			array('check', 'rtg_stats_enable'),
			array('check', 'rtg_notification_enable'),
			array('text', 'rtg_per_profile_page'),
			array('text', 'rtg_in_notification'),
			array('check', 'rtg_show_like_on_boards'),
			array('check', 'rtg_show_total_like_in_posts'),
		);

		require_once($sourcedir . '/ManageServer.php');
		saveDBSettings($general_settings);
		redirectexit('action=admin;area=likeposts;sa=generalsettings');
	}

	public function permissionSettings() {
		global $txt, $context, $sourcedir;

		require_once($sourcedir . '/ManageServer.php');
		require_once($sourcedir . '/Subs-Membergroups.php');

		// set up the vars for groups and guests permissions
		$context['like_posts']['groups_permission_settings'] = array(
			'rtg_can_like_posts',
			'rtg_can_view_likes',
			'rtg_can_view_others_likes_profile',
			'rtg_can_view_likes_stats',
			'rtg_can_view_likes_notification'
		);

		$context['like_posts']['guest_permission_settings'] = array(
			'rtg_guest_can_view_likes_in_posts',
			'rtg_guest_can_view_likes_in_boards',
			'rtg_guest_can_view_likes_in_profiles',
			'rtg_guests_can_view_likes_stats'
		);

		$context['like_posts']['groups'][0] = array(
			'id_group' => 0,
			'group_name' => $txt['rtg_regular_members'],
		);
		$context['like_posts']['groups'] += list_getMembergroups(null, null, 'id_group', 'regular');
		unset($context['like_posts']['groups'][3]);
		unset($context['like_posts']['groups'][1]);
		$context['like_posts']['groups'] += list_getMembergroups(null, null, 'id_group', 'post_count');		

		$context['page_title'] = $txt['rtg_admin_panel'];
		$context['sub_template'] = 'rtg_admin_permission_settings';
		$context['like_posts']['tab_name'] = $txt['rtg_permission_settings'];
		$context['like_posts']['tab_desc'] = $txt['rtg_permission_settings_desc'];
	}

	public function savePermissionsettings() {
		global $context;

		/* I can has Adminz? */
		isAllowedTo('admin_forum');
		checkSession('request', '', true);
		unset($_POST['submit']);

		// set up the vars for groups and guests permissions
		$context['like_posts']['groups_permission_settings'] = array(
			'rtg_can_like_posts',
			'rtg_can_view_likes',
			'rtg_can_view_others_likes_profile',
			'rtg_can_view_likes_stats',
			'rtg_can_view_likes_notification'
		);

		$context['like_posts']['guest_permission_settings'] = array(
			'rtg_guest_can_view_likes_in_posts',
			'rtg_guest_can_view_likes_in_boards',
			'rtg_guest_can_view_likes_in_profiles',
			'rtg_guests_can_view_likes_stats'
		);

		$permissionKeys = array(
			'rtg_can_like_posts',
			'rtg_can_view_likes',
			'rtg_can_view_others_likes_profile',
			'rtg_can_view_likes_stats',
			'rtg_can_view_likes_notification',
		);

		$guestPermissionKeys = array(
			'rtg_guest_can_view_likes_in_posts',
			'rtg_guest_can_view_likes_in_boards',
			'rtg_guest_can_view_likes_in_profiles',
			'rtg_guests_can_view_likes_stats'
		);

		// Array to be saved to DB
		$general_settings = array();
		// Array to be submitted to DB
		foreach($_POST as $key => $val) {
			if(in_array($key, $context['like_posts']['groups_permission_settings'])) {
				// Extract the user permissions first
				if(array_filter($_POST[$key], 'is_numeric') === $_POST[$key]) {
					if(($key1 = array_search($key, $permissionKeys)) !== false) {
						unset($permissionKeys[$key1]);
					}
					$_POST[$key] = implode(',', $_POST[$key]);
					$general_settings[] = array($key, $_POST[$key]);
				}
			} elseif(in_array($key, $context['like_posts']['guest_permission_settings'])) {
				// Extract the guest permissions as well
				if(is_numeric($_POST[$key])) {
					if(($key1 = array_search($key, $guestPermissionKeys)) !== false) {
						unset($guestPermissionKeys[$key1]);
					}
					$general_settings[] = array($key, $_POST[$key]);
				}
			}
		}

		// Remove the keys which were saved previously but removed this time
		if(!empty($permissionKeys)) {
			foreach ($permissionKeys as $value) {
				$general_settings[] = array($value, '');
			}
		}

		if(!empty($guestPermissionKeys)) {
			foreach ($guestPermissionKeys as $value) {
				$general_settings[] = array($value, '');
			}
		}
		$this->dbInstance->updatePermissions($general_settings);
		redirectexit('action=admin;area=likeposts;sa=permissionsettings');
	}

	public function boardsettings() {
		global $txt, $context, $sourcedir, $cat_tree, $boards, $boardList;

		require_once($sourcedir . '/Subs-Boards.php');
		$context['page_title'] = $txt['rtg_admin_panel'];
		$context['sub_template'] = 'rtg_admin_board_settings';
		$context['like_posts']['tab_name'] = $txt['rtg_board_settings'];
		$context['like_posts']['tab_desc'] = $txt['rtg_board_settings_desc'];
		getBoardTree();

		$context['categories'] = array();
		foreach ($cat_tree as $catid => $tree) {
			$context['categories'][$catid] = array(
				'name' => &$tree['node']['name'],
				'id' => &$tree['node']['id'],
				'boards' => array()
			);

			foreach ($boardList[$catid] as $boardid) {
				$context['categories'][$catid]['boards'][$boardid] = array(
					'id' => &$boards[$boardid]['id'],
					'name' => &$boards[$boardid]['name'],
					'child_level' => &$boards[$boardid]['level'],
				);
			}
		}
	}

	public function saveBoardsettings() {
		/* I can has Adminz? */
		isAllowedTo('admin_forum');
		checkSession('request', '', true);

		$general_settings = array();
		$activeBoards = $_POST['active_board'];
		$activeBoards = isset($activeBoards) && !empty($activeBoards) ? implode(',', $activeBoards) : '';
		$general_settings[] = array('rtg_active_boards', $activeBoards);

		$this->dbInstance->updatePermissions($general_settings);
		redirectexit('action=admin;area=likeposts;sa=boardsettings');
	}

	public function recountLikeStats() {
		global $txt, $context;

		isAllowedTo('admin_forum');
		$context['page_title'] = $txt['rtg_admin_panel'];
		$context['sub_template'] = 'rtg_admin_recount_stats';
		$context['like_posts']['tab_name'] = $txt['rtg_recount_stats'];
		$context['like_posts']['tab_desc'] = $txt['rtg_recount_stats_desc'];
	}

	public function optimizeLikes() {
		isAllowedTo('admin_forum');

		// Lets fire the bullet.
		@set_time_limit(300);
		$this->dbInstance->optimizeLikes();

		$resp = array('result' => true);
		return ReportToGithub::$ReportToGithubUtils->sendJSONResponse($resp);
	}

	public function removeDupLikes() {
		isAllowedTo('admin_forum');

		$this->dbInstance->removeDupLikes();
		$resp = array('result' => true);
		return ReportToGithub::$ReportToGithubUtils->sendJSONResponse($resp);
	}

	public function recountLikesTotal() {
		isAllowedTo('admin_forum');

		// Lets fire the bullet.
		@set_time_limit(300);

		$startLimit = (int) $_REQUEST['startLimit'];
		$endLimit = (int) $_REQUEST['endLimit'];
		$totalWork = (int) $_REQUEST['totalWork'];

		// Result carries totalWork to do
		$result = $this->dbInstance->recountLikesTotal($startLimit, $totalWork);

		$resp = array('totalWork' => (int) $result, 'endLimit' => (int) $endLimit);
		return ReportToGithub::$ReportToGithubUtils->sendJSONResponse($resp);
	}
}

?>
