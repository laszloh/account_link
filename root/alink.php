<?php
/**
*
* @author Laszlo Imre Hegedüs laszlo.hegedues@gmail.com
*
* @package alink
* @copyright (c) 2008 Laszlo Imre Hegedüs
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/account_link');

// Exclude Bots
if ($user->data['is_bot'])
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}

//Check if you are locked or not
if (!$auth->acl_get('u_use_alink'))
{
	trigger_error('NOT_AUTHORISED');
}

// get all reqired values
$new_user = request_var('i', 0);
$u_redirect = request_var('r', "{$phpbb_root_path}index.$phpEx");

// sanitize input
if($new_user === 0)
{
	// log a conclusive error
	add_log('user', 'ALINK_SWITCH_NO_UID', $user->data['username']);

	// display the default error to the user
	meta_refresh(3, $u_redirect);
	trigger_error($user->lang['ALINK_GENERAL_ERROR']);	
}

// create redirect text
if ($u_redirect === "{$phpbb_root_path}index.$phpEx" || $u_redirect === "index.$phpEx" || $u_redirect === $_SERVER['REQUEST_URI'])
{
	$l_redirect = $user->lang['RETURN_INDEX'];
}
else
{
	$l_redirect = $user->lang['RETURN_PAGE'];
}

// test if current user and new user are linked together
if (!$alink->is_account_linked(0, $new_user))
{
	add_log('user', 'LOG_ACCOUNT_NOT_LINKED', $user->data['username'], implode(' ', $username_ary));

	meta_refresh(3, $u_redirect);
	trigger_error($user->lang['ACCOUNT_NOT_LINKED'];);
}

// append/replace SID (may change during the session for AOL users)
$u_redirect = reapply_sid($u_redirect);
$redirect = '<br /><br />' . sprintf($l_redirect, '<a href="' . $u_redirect . '">', '</a>');

$persist_login = false;
if ($user->cookie_data['k'] != '')
{
	$persist_login = true;
}

// do the account switching
// Based heavily on CB Connector by geeffland (http://cbconnector.com)
$user->session_begin();
$auth->acl($user->data);
$user->setup();	//'ucp'
$admin = false;
$viewonline = true;
$user->cookie_data['k'] = '';
$result = $user->session_create($new_user, $admin, $persist_login, $viewonline);

// we are done generate output
if ($result === true)
{
	$message = sprintf($user->lang['ACCOUNT_SWITCH_REDIRECT'], $user->data['username']);
	
	meta_refresh(3, reapply_sid($u_redirect));
	trigger_error($message . $redirect);
}
else
{
	meta_refresh(3, $u_redirect);
	trigger_error('ACCT_SWITCH_ERROR' . $redirect);
}

?>
