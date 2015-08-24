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

// If SSI.php is in the same place as this file, and SMF isn't defined, this is being run standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as SMF\'s index.php.');

remove_integration_function('integrate_pre_include', '$sourcedir/Subs-LDAP.php');
remove_integration_function('integrate_admin_areas', 'LDAPAdminMenuHook');
remove_integration_function('integrate_load_theme', 'LDAPLoadThemeHook');
//remove_integration_function('integrate_validate_login', 'LDAPLoginHook');
//remove_integration_function('integrate_other_passwords', 'LDAPOtherPasswordHook');

//At uninstall, we disable LDAP and remove LDAP user data in $modSettings
updateSettings(array(
	'ldap_enabled' => 0,
	'ldap_user' => '',
	'ldap_password' => '',
	),
false);

?>