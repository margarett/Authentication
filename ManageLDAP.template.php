<?php
/**
* Authentication
*
* @package Authentication
* @author Adam Tallon
* @copyright 2013 Adam Tallon
* @license See license.txt file
*
* @version 0.3
*/

function template_test_ldap()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<div id="manage_maintenance">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['ldap_test_title'], '</h3>
		</div>
		<div class="windowbg">
			<div class="content">';
			
		//Does the ldap_connect function exists? If not, this can't work!!!
		if (!function_exists('ldap_connect'))
		{
			echo '
				<div class="errorbox">', $txt['ldap_php_disabled'], '</div>';
			$errors = true;
		}
		// Is LDAP enabled?
		else if (empty($modSettings['ldap_enabled']))
		{
			echo '
				<div class="errorbox">', $txt['ldap_config_disabled'], '</div>';
			$errors = true;
		}
		
		//Now, test results?
		if (!empty($context['ldap_test_error'])) //Test failed
		{
			echo '
				<div class="errorbox">', $txt['ldap_test_error'], '</div>';
		}
		if (!empty($context['ldap_test_success'])) //Test succeeded, yay!
		{
			echo '
				<div class="maintenance_finished">', $txt['ldap_test_success'], '</div>';
		}

		// No errors? Show the "test" button
		if (empty($errors))
		{

			echo '
				<form action="', $context['post_url'], ';', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
					<p>', $txt['ldap_test_info'], '</p>';
		
			echo '
					<input type="submit" value="', $txt['ldap_run_now'], '" class="button_submit" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';
		}

		echo '
			</div>
		</div>
	</div>';
}

?>