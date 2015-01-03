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
 

if (!defined('ELK')) {
	die('Hacking attempt...');
}

class ReportToGithubDB {
	public function __construct() {}

	/*
	* retrieve github credentials
	* @param array $replaceArray
	*/
	public function getCredentials() {
		$db = database();

		$data = array();
		$request = $db->query('', '
			SELECT repo, owner, username, password, hash
			FROM {db_prefix}report_to_github_creds'
		);

		list ($data['rtg_github_repo'], $data['rtg_github_owner'], $data['rtg_github_username'], $data['rtg_github_password'], $data['rtg_github_hash']) = $db->fetch_row($request);
		$db->free_result($request);
		return $data;
	}

	/*
	* to update github credentials
	* @param array $replaceArray
	*/
	public function updateCredentials($replaceArray) {
		global $smcFunc;

		$smcFunc['db_insert']('replace',
			'{db_prefix}report_to_github_creds',
			array('repo' => 'string-255', 'owner' => 'string-255', 'username' => 'string-255',
				'password' => 'string-255', 'hash' => 'string-255'),
			$replaceArray,
			array('variable')
		);
	}
}

?>
