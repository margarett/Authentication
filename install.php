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
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
	require_once(dirname(__FILE__) . '/SSI.php');
} elseif (!defined('SMF')) {
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

add_integration_function('integrate_pre_include', '$sourcedir/Subs-LDAP.php');
add_integration_function('integrate_admin_areas', 'LDAPAdminMenuHook');
add_integration_function('integrate_validate_login', 'LDAPLoginHook');
add_integration_function('integrate_other_passwords', 'LDAPOtherPasswordHook');

updateSettings(array(
	'ldap_port' => '389', //Default port for LDAP is 389
	'ldap_protocol_version' => '3',
	'ldap_referrals' => '0',
	'ldap_search_filter' => '(cn=*)',
	'ldap_attrib_user_login' => 'samaccountname',
	'ldap_attrib_email' => 'mail',
	'ldap_primary_membergroup' => '0',
	),
false);
?>