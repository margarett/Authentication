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

// Admin Menu Labels
$txt['admin_auth_ldap_title'] = 'LDAP Authentication';
$txt['admin_auth_ldap_settings'] = 'LDAP Settings';
$txt['admin_auth_ldap_test'] = 'Test LDAP';

// Section & Sub-section labels
$txt['auth_title'] = 'Authentication';
$txt['auth_description'] = 'Manage and setup different authentication methods for your forum.';
$txt['ldap_test_title'] = 'Run LDAP Connection Test';
$txt['ldap_settings_title'] = 'LDAP Settings';

// Connection Error
$txt['ldap_disabled'] = 'LDAP disabled, a user tried to login with: ';

// Test Labels
$txt['ldap_test_error'] = 'LDAP connection test failed. Please check your configuration';
$txt['ldap_test_error_server_said'] = 'The server said:';
$txt['ldap_test_success'] = 'LDAP connection test succeeded. Congratulations, your users can now use LDAP for authentication';
$txt['ldap_conn_not_set'] = 'Some credentials missing, unable to proceed.';
$txt['ldap_conn_failed'] = 'Failed to connect to LDAP server.';
$txt['ldap_bind_failed'] = 'Failed to bind LDAP details.';
$txt['ldap_search_error'] = 'Error searching for user on authentication server.';
$txt['ldap_no_results_error'] = 'Error, no results found.';
$txt['ldap_login_failed'] = 'Failed to login onto LDAP server.';
$txt['ldap_unable_to_add_member'] = 'Unable to add new member: ';
$txt['ldap_unable_to_update_member'] = 'Unable to update member ID ';
$txt['ldap_test_info'] = 'This task will allow you to properly test your LDAP configuration.';
$txt['ldap_run_now'] = 'Run now';

// Setting Labels
$txt['ldap_enabled'] = 'Enable LDAP support';
$txt['ldap_host'] = 'Server';
$txt['ldap_port'] = 'Port';
$txt['ldap_user'] = 'Username';
$txt['ldap_password'] = 'Password';
$txt['ldap_protocol_version'] = 'Protocol Version';
$txt['ldap_referrals'] = 'Allow Referrals';
$txt['ldap_dn'] = 'Distinguished Name (DN)';
$txt['ldap_username_extension'] = 'Username Extension';
$txt['ldap_search_filter'] = 'Search Filter';
$txt['ldap_group'] = 'Location (OU) of LDAP users';
$txt['ldap_attrib_user_login'] = 'Default AD Username Field';
$txt['ldap_attrib_email'] = 'Default AD Email Field';
$txt['save_ldap_settings'] = 'Save';
$txt['ldap_primary_membergroup'] = 'Target Membergroup';
$txt['ldap_membergroup_default'] = '(no primary membergroup)';

//Errors in configuration
$txt['ldap_config_disabled'] = 'LDAP support is disabled, check LDAP Settings';
$txt['ldap_php_disabled'] = 'LDAP functions in PHP are not enabled! This MOD can\'t work without those';

?>