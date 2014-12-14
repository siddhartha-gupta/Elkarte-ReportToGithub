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

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('ELK'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as Elkarte\'s index.php.');

// Add hooks and plugin the mod
add_integration_function('integrate_pre_include', 'SOURCEDIR/controllers/ReportToGithub/ReportToGithub.php', true);
add_integration_function('integrate_load_theme', 'ReportToGithub::includeAssets', true);
add_integration_function('integrate_admin_areas', 'ReportToGithub::addAdminPanel', true);
add_integration_function('integrate_create_topic', 'ReportToGithub::onPostCreated', true);

?>
