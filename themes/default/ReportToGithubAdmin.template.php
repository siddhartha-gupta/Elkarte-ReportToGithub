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

function template_rtg_admin_general_settings() {
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<form id="admin_form_wrapper" action="'. $scripturl .'?action=admin;area=reporttogithub;sa=savegeneralsettings" method="post" accept-charset="UTF-8">
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';

					foreach ($context['config_vars'] as $config_var) {
						echo '
						<dl class="settings">
							<dt>
								<span>'. $txt[$config_var['name']] .'</span>';
								if (isset($config_var['subtext']) && !empty($config_var['subtext'])) {
									echo '
									<br /><span class="smalltext">', $config_var['subtext'] ,'</span>';
								}
							echo '
							</dt>
							<dd>';

							if($config_var['type'] === 'check') {
								echo '
								<input type="checkbox" name="', $config_var['name'], '" id="', $config_var['name'], '"', ($config_var['value'] ? ' checked="checked"' : ''), ' value="1" class="input_check" />';
							}

							echo '
							</dd>
						</dl>';
					}

					// $context['session_var'] . '=' . $context['session_id']
					echo '
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					<input type="hidden" name="', $context['admin-dbsc_token_var'], '" value="', $context['admin-dbsc_token'], '" />
					<input type="submit" name="submit" value="', $txt['rtg_submit'], '" tabindex="', $context['tabindex']++, '" class="button_submit" />';
		
					echo '
					</div>
				<span class="botslice"><span></span></span>
			</div>
	
		</form>
	</div>
	<br class="clear">';
}

function template_rtg_admin_recount_stats() {
	global $context, $txt, $scripturl, $modSettings;

	echo '
	<div id="admincenter">
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
				<div class="content">';

	echo '
				<fieldset><legend>', $txt['rtg_recount_likes'], '</legend>
					<div class="rtg_admin_recount_text">
						', $txt['rtg_check_likes'], '
					</div>
					<div class="rtg_admin_recount_btn">
						<span class="floatright">
							<input type="submit" value="Run task now" class="button_submit" onclick="lpObj.likePostsAdmin.optimizeLikes(event); return false;">
						</span>
					</div>
					<div class="member_count_precentage"></div>
				</fieldset>';

	echo '
				<fieldset><legend>', $txt['rtg_remove_duplicate_likes'], '</legend>
					<div class="rtg_admin_recount_text">
						', $txt['rtg_remove_duplicate_likes_desc'], '
					</div>
					<div class="rtg_admin_recount_btn">
						<span class="floatright">
							<input type="submit" value="Run task now" class="button_submit" onclick="lpObj.likePostsAdmin.removeDupLikes(event, {}); return false;">
						</span>
					</div>
					<div class="member_count_precentage"></div>
				</fieldset>';

	echo '
				<fieldset><legend>', $txt['rtg_recount_total_likes'], '</legend>
					<div class="rtg_admin_recount_text">
						', $txt['rtg_reset_total_likes_received'], '
					</div>
					<div class="rtg_admin_recount_btn">
						<span class="floatright">
							<input type="submit" value="Run task now" class="button_submit" onclick="lpObj.likePostsAdmin.recountStats(event, {}); return false;">
						</span>
					</div>
					<div class="member_count_precentage"></div>
				</fieldset>';

	echo '
				</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<div class="like_posts_overlay hide_elem"></div>
	<br class="clear">';
}

?>
