<?php
/**
*
* @author Drakkim (Michael Flenniken, Jr.) drakkim@conclavewiz.com
* @package umil
* @version $Id account_link.php 0.7.4 2009-10-19 05:05:55GMT Drakkim $
* @copyright (c) 2009 Michael Flenniken, Jr.
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @todo add install module info (since UMIF gen didn't...?)
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/account_link');

if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// We only allow a founder to install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		login_box('', 'LOGIN');
	}
	trigger_error('NOT_AUTHORISED');
}

if (!class_exists('umil'))
{
	include($phpbb_root_path . 'umil/umil.' . $phpEx);
}

$umil = new umil(true);

$mod = array(
	'name'		=> 'Account Links',
	'version'	=> '0.7.4',
	'config'	=> 'account_links_version',
	'enable'	=> 'account_links_enable',
);

if (confirm_box(true))
{
	// Install the base 0.7.4 version
	if (!$umil->config_exists($mod['config']))
	{
		// Lets add a config setting for enabling/disabling the MOD and set it to true
		$umil->config_add($mod['enable'], true);

		// We must handle the version number ourselves.
		$umil->config_add($mod['config'], $mod['version']);

		$umil->table_add(array(		));

		$umil->table_column_add('USERS_TABLE', 'master_id', array('UINT', '0'));

		// Our final action, we purge the board cache
		$umil->cache_purge();
	}

	// We are done
	trigger_error('Done!');
}
else
{
	confirm_box(false, 'INSTALL_TEST_MOD');
}

// Shouldn't get here.
redirect($phpbb_root_path . $user->page['page_name']);

?>