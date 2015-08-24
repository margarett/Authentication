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

if (!defined('SMF')) {
	die('No direct access...');
}

/*
 * Hook for initializations
 * If LDAP is active, disable client side hashing
 *
 */
function LDAPLoadThemeHook(&$admin_areas)
{
	global $modSettings, $context;

	if (!empty($modSettings['ldap_enabled']))
		$context['disable_login_hashing'] = true;
}

/*
 * Adds the authentication options to the admin area menu.
 * 
 * @param array $admin_areas
 */
function LDAPAdminMenuHook(&$admin_areas)
{
	global $txt;

	loadLanguage('ManageLDAP');
	
	$admin_areas['config']['areas']['auth'] = array(
		'label' => $txt['admin_auth_ldap_title'],
		'file' => 'ManageLDAP.php',
		'function' => 'ManageLDAP',
		'icon' => 'ldap.png',
		'permission' => array('admin_forum'),
		'subsections' => array(
			'test_ldap' => array($txt['admin_auth_ldap_test']),
			'settings_ldap' => array($txt['admin_auth_ldap_settings']),
		),
	);
}

/*
 * This function connects to the LDAP server and checks all the
 * login credentials to validate them.
 *
 * @param: selection of choosing $modSettings user or login details
 * @returns an array of ldap variables
 */
function connectLDAPServer($modSettings_user = false)
{
	global $modSettings, $txt;

	$ConnData = array();

	// Basic testing. Every field needs to be filled...
	if (empty($modSettings['ldap_enabled']) || empty($modSettings['ldap_host']) || empty($modSettings['ldap_user']) ||
			empty($modSettings['ldap_dn']) || empty($modSettings['ldap_password']))
	{
		$ConnData['error']['LDAP_ErrMsg'] = $txt['ldap_conn_not_set'];
		return $ConnData;
	}

	//389 is the default port.
	if (empty($modSettings['ldap_port']))
		$modSettings['ldap_port'] = 389;

	//Basic tests passed, let's try the connection itself
	$ConnData['ldapconn'] = ldap_connect($modSettings['ldap_host'], $modSettings['ldap_port']);
	// Setup LDAP options
	ldap_set_option($ConnData['ldapconn'], LDAP_OPT_PROTOCOL_VERSION, $modSettings['ldap_protocol_version']);
	ldap_set_option($ConnData['ldapconn'], LDAP_OPT_REFERRALS, isset($modSettings['ldap_referrals']) ? $modSettings['ldap_referrals'] : 0);
	// Bind. For the "bind" username we need the complete DN...
	if ($modSettings_user)
	{
		$user = 'cn=' . $modSettings['ldap_user'] . (empty($modSettings['ldap_username_extension']) ? '' : $modSettings['ldap_username_extension']);
		$user .= empty($modSettings['ldap_dn']) ? '' : ',' . $modSettings['ldap_dn'];
		$ConnData['ldapbind'] = @ldap_bind($ConnData['ldapconn'], $user, base64_decode($modSettings['ldap_password']));
	}
	else
	{
		$user = 'cn=' . strtolower($_POST['user']) . (empty($modSettings['ldap_username_extension']) ? '' : $modSettings['ldap_username_extension']);
		$user .= empty($modSettings['ldap_dn']) ? '' : ',' . $modSettings['ldap_dn'];
		$ConnData['ldapbind'] = @ldap_bind($ConnData['ldapconn'], $user, $modSettings['ldap_password']);
	}
	if (!$ConnData['ldapbind'])
		$ConnData['error'] = getLDAPError($ConnData['ldapconn']);

	return $ConnData;
}

/*
 * This function simply closes the connection to the LDAP server
 *
 * @param LDAP link identifier from ldap_connect
 * @return
 */
function closeLDAPServer($link_identifier)
{
	ldap_close($link_identifier);
}

/*
 * This function returns the last error of the LDAP service
 *
 * @param LDAP link identifier from ldap_connect
 * @return an array with Error number and description
 */
function getLDAPError($link_identifier)
{
	$error = array();
	$error['LDAP_ErrNum'] = ldap_errno($link_identifier);
	$error['LDAP_ErrMsg'] = ldap_error($link_identifier);
	
	return $error;	
}

/*
 * This function performs the LDAP work at login.
 * It connects to LDAP server and searches for the given user. If found,
 * updates SMF's members table so that SMF's login process can handle it
 * naturally.
 * login credentials to validate them.
 *
 * @param
 * @return
 */
function doLoginStuffLDAP()
{
	$ConnData = array();
	// First things first, try the connection with the user in the configuration
	// as we want to search for the user loggin' in
	$ConnData = connectLDAPServer(true);
	// Error. Not a problem. No LDAP, just that...
	if (empty($ConnData['ldapbind']) || empty($ConnData['error']))
		return;
}

/*
 * This function does all the work, connecting to the LDAP server and checking all the
 * login credentials to validate them.
 *
 * @param string $type
 * @param array $login_details
 * @return string, 'retry' if failed authentication or an empty string if passed authentication.
 */
function contactLDAPServer($type = '', $login_details = array())
{
	global $context, $txt, $smcFunc, $scripturl, $modSettings, $sourcedir;

	if (!isset($txt['ldap_bind_failed'])) //Maybe we need to reload our language?
		loadLanguage('ManageLDAP');

	$vUserCount = 0;
	$vUserList = null;
	$vProcessing = null;

	// Is LDAP enabled?
	if (isset($modSettings['ldap_enabled']) && $modSettings['ldap_enabled']) {
		// Set LDAP connection
		if (isset($modSettings['ldap_host']) && isset($modSettings['ldap_port'])) {
			$ldapconn = ldap_connect($modSettings['ldap_host'], $modSettings['ldap_port']);

			// Did we connect?
			if ($ldapconn) {
				// Setup LDAP options
				ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, $modSettings['ldap_protocol_version']);
				ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, isset($modSettings['ldap_referrals']) ? $modSettings['ldap_referrals'] : 0);
				if (!empty($modSettings['ldap_user']) && !empty($modSettings['ldap_password']))
				{
					//For the "bind" username we need the complete DN...
					$user = 'cn=';
					$user .= $modSettings['ldap_user'] . (empty($modSettings['ldap_username_extension']) ? '' : $modSettings['ldap_username_extension']);
					//$user .= empty($modSettings['ldap_default_group']) ? '' : ',ou=' . $modSettings['ldap_default_group'];
					$user .= empty($modSettings['ldap_dn']) ? '' : ',' . $modSettings['ldap_dn'];
					$ldapbind = ldap_bind($ldapconn, $user, $modSettings['ldap_password']);
				}
				else
				{
					$ldapbind = ldap_bind($ldapconn);
				}

				if ($ldapbind) {
					// Are we syncing members?
					if ($type == 'sync') {
						// Lets perform an LDAP search
						if (isset($modSettings['ldap_dn']) && isset($modSettings['ldap_search_filter'])) {
							$ldapsearch = @ldap_search($ldapconn, $modSettings['ldap_dn'], $modSettings['ldap_search_filter']);
						}

						if ($ldapsearch) {
							// Search of members successful? Get results and count
							$ldapCount = @ldap_count_entries($ldapconn, $ldapsearch);

							if ($ldapCount >= 1) {
								// Get all entries
								$ldapEntries = ldap_get_entries($ldapconn, $ldapsearch);

								require_once($sourcedir . '/Subs-Members.php');

								// Process each result found
								for ($i = 0; $i < count($ldapEntries); $i++) {
									if (!empty($ldapEntries[$i]) && isset($modSettings['ldap_attrib_email']) && !empty($ldapEntries[$i][$modSettings['ldap_attrib_email']][0])) {
										if (isset($modSettings['ldap_attrib_user_login']) && !memberExist($ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0])) {
											$vProcessing = $ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0].' (A)';
											
											// Register new member
											$regOptions = array(
												'interface' => 'admin',
												'username' => $ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0],
												'email' => $ldapEntries[$i][$modSettings['ldap_attrib_email']][0],
												'password' => '',
												'password_check' => '',
												'check_reserved_name' => true,
												'check_password_strength' => false,
												'check_email_ban' => false,
												'send_welcome_email' => false,
												'require' => 'nothing',
												'memberGroup' => !empty($modSettings['ldap_primary_membergroup']) ? (int) $modSettings['ldap_primary_membergroup'] : 0,
												'auth_method' => 'ldap',
												'hide_email' => 1,
											);

											if (!registerMember($regOptions, true)) {
												log_error($txt['ldap_unable_to_add_member'].$ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0]);
											}
										} else {
											if (isset($modSettings['ldap_attrib_user_login']) && isset($modSettings['ldap_attrib_email'])) {
												$vProcessing = $ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0].' (U)';
												
												// Update existing member
												$updateData = array(
													'email_address' => $ldapEntries[$i][$modSettings['ldap_attrib_email']][0],
												);

												$memberID = getMemberID($ldapEntries[$i][$modSettings['ldap_attrib_user_login']][0]);

												if (updateMemberData($memberID , $updateData)) {
													log_error($txt['ldap_unable_to_update_member'].$memberID.'.');
												}
											} else {	
												log_error($txt['ldap_unable_to_update_member'].$memberID.'.');
												ldap_close($ldapconn);
												return 'retry';
											}
										}
									}

									// Check if user has already been counted
									if (!strstr($vUserList, $vProcessing)) {
										$vUserCount++;
										$vUserList .= $vProcessing.'<br/>';
									}
								}

								$context['body'] = sprintf($txt['ldap_sync_completed'], $vUserCount, $vUserList);
							} else {
								ldap_close($ldapconn);
								$context['body'] = $txt['ldap_no_results_error'];
								log_error($txt['ldap_no_results_error']);
							}
						} else {
							ldap_close($ldapconn);
							$context['body'] = $txt['ldap_search_error'];
							log_error($txt['ldap_search_error']);
						}
					} else {
						// LDAP login
						// Lets perform an LDAP search
						if ($modSettings['ldap_attrib_user_login']) {
							$filter = sprintf('('.$modSettings['ldap_attrib_user_login'].'=%s)', $login_details['username']);
						}

						if (isset($modSettings['ldap_dn'])) {
							$ldapsearch = @ldap_search($ldapconn, $modSettings['ldap_dn'], $filter);
						}
						
						if ($ldapsearch) {
							// Search of members successful? Get results and count
							$ldapCount = @ldap_count_entries($ldapconn, $ldapsearch);
							
							if ($ldapCount != 1) {
								ldap_close($ldapconn);
								return 'retry';
							}
							
							// Get all entries
							$ldapEntries = ldap_get_entries($ldapconn, $ldapsearch);
							
							// Rebind using member's full DN and supplied password
							if (isset($modSettings['ldap_username_extension'])) {
								$member_login_bind = $login_details['username'].$modSettings['ldap_username_extension'];
							}

							$member_bind = @ldap_bind($ldapconn, $member_login_bind, $login_details['password']);
							
							if (!$member_bind || !isset($member_bind)) {
								// Unable to bind
								ldap_close($ldapconn);
								return 'retry';
							} else {
								if (isset($modSettings['ldap_attrib_user_login'])) {
									// Perform second verification search
									$member_search = ldap_search($ldapconn, $modSettings['ldap_dn'], $filter);
									$member_info = ldap_get_entries($ldapconn, $member_search);
									
									if ($login_details['username'] == $member_info[0][$modSettings['ldap_attrib_user_login']][0]) {
										ldap_close($ldapconn);
										return;
									} else {
										ldap_close($ldapconn);
										return 'retry';
									}
								} else {
									ldap_close($ldapconn);
									return 'retry';
								}
							}
						} else {
							// Search found no results, close LDAP connection to the server
							ldap_close($ldapconn);
							$context['body'] = $txt['ldap_search_error'];
							log_error($txt['ldap_search_error']);
							return 'retry';
						}				
					}
				} else {
					ldap_close($ldapconn);
					fatal_error($txt['ldap_bind_failed']);
					return 'retry';
				}
			} else {
				ldap_close($ldapconn);
				fatal_error($txt['ldap_conn_failed']);
				return 'retry';
			}
		} else {
			fatal_error($txt['ldap_conn_not_set']);
			return 'retry';
		}
	} else {
		log_error($txt['ldap_disabled'].$_POST['user']);
		return 'retry';
	}
}

/*
 * Checks of the member by username if they already exist in the members table
 *
 * @param string $member_login
 * @return bool, retruns true if the member exists, otherwise it will return false
 */
function memberExist($member_login)
{
	global $smcFunc;

	$memberExists = false;

	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE member_name = {string:username}
		LIMIT 1',
		array(
				'username' => $member_login,
		)
	);
	
	if ($smcFunc['db_num_rows']($request) != 0) {
		$memberExists = true;
	}
	
	$smcFunc['db_free_result']($request);

	return $memberExists;
}

/*
 * Retrieves the members unique id from the members tables based off their username
 *
 * @param string $member_login
 * @return int, returns the members unique id
*/
function getMemberID($member_login)
{
	global $smcFunc;

	$memberID = 0;

	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE member_name = {string:username}
		LIMIT 1',
		array(
				'username' => $member_login,
		)
	);
	
	while ($row = $smcFunc['db_fetch_assoc']($request)) {
		$memberID = $row['id_member'];
	}
	
	$smcFunc['db_free_result']($request);

	return $memberID;
}

/*
 * Generates a 12 character string. Password is never used, this is just to satisfy SMF functionality.
 *
 * @return string, returns a randomly generated string
*/
function rand_password()
{
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$newPassword = substr(str_shuffle($chars), 0, rand(12,12));
	
	return $newPassword;
}

?>