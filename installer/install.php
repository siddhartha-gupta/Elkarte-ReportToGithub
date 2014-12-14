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
*
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('ELK'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as Elkarte\'s index.php.');

createTables();

// adding default mod settings
checkVersion1_0Upgrade();

// at last just update the mod version
updateModVersion('1.0');

function createTables() {
	$dbtbl = db_table();

	// Table structure for report to github
	$tables = array(
		'report_to_github' => array (
			'columns' => array (
				array(
					'name' => 'id_rtg',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'auto' => true,
				),
				array(
					'name' => 'id_msg',
					'type' => 'int',
					'size' => 10,
					'unsigned' => true,
					'default' => '0',
				),
				array(
					'name' => 'id_github',
					'type' => 'mediumint',
					'size' => 8,
					'unsigned' => true,
					'default' => '0',
				)
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_rtg', 'id_msg'),
				),
			),
		)
	);

	// create the tables if not created
	foreach ($tables as $table => $data) {
		$dbtbl->db_create_table('{db_prefix}' . $table, $data['columns'], $data['indexes']);
	}
}

function checkVersion1_0Upgrade() {
	global $smcFunc;

	$newVersion = isRunningLatestVersion('1.0');

	if($newVersion) {
		updateSettings(
			array(
				'rtg_mod_version' => '1.0',
				'rtg_mod_enable' => 0,
				'rtg_active_boards' => ''
			)
		);
	}
}

/**
 * @param string $versionToCheck
 */
function isRunningLatestVersion($versionToCheck) {
	$db = database();

	$request = $db->query('', '
		SELECT value
		FROM {db_prefix}settings
		WHERE variable =  {string:report_to_github_mod_version}
		LIMIT 1',
		array(
			'report_to_github_mod_version' => 'report_to_github_mod_version',
		)
	);

	if ($db->num_rows($request) == 0) {
		$newVersion = true;
	} else {
		list ($last_version) = $db->fetch_row($request);
		if (version_compare($versionToCheck, $last_version) > 0) {
			$newVersion = true;
		} else {
			$newVersion = false;
		}
	}
	$db->free_result($request);
	return $newVersion;
}

function updateModVersion($newVersion) {
	$db = database();

	$request = $db->query('', '
		UPDATE {db_prefix}settings
		SET value = {string:current_version}
		WHERE variable = {string:report_to_github_mod_version}',
		array(
			'current_version' => $newVersion,
			'report_to_github_mod_version' => 'report_to_github_mod_version'
		)
	);
}

if (ELK == 'SSI')
	echo 'Database adaptation successful!';
