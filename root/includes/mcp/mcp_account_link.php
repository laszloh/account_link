<?php
/**
*
* @author Michael Flenniken, Jr drakkim@conclavewiz.com
*
* @package mcp
* @version SVN: $Id: mcp_account_link.php 77 2012-09-02 10:48:53Z drakkim $
* @copyright (c) 2008 Michael Flenniken, Jr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
defined( 'IN_PHPBB' ) or die('Hacking Attempt!');

/**
*/
require_once($phpbb_root_path . 'includes/functions_account_link.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

/**
* @package mcp
*/
class mcp_account_link
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $template, $phpbb_root_path, $phpEx, $db;
		global $account_link_config;
		
		if (empty($account_link_config))
		{
			$account_link_config = account_link_config();
		}
		
		$user->add_lang('mods/account_link');
		$submit = request_var('submit','', true);
		
		$is_master = false;
		$master_id=0;
		$master_name='';
		
		$s_hidden_fields = '';
		$error='';
//		$error = request_var('error','');
		switch ($mode)
		{
			case 'manage':
				$this->page_title = 'Manage Linked Accounts';
				$this->tpl_name = 'mcp_account_link_manage';
				if ($user->data['master_id'] != 0)
				{
					$is_master=false;
					
					$sql = 'SELECT username
					FROM ' . USERS_TABLE . '
						WHERE user_id = '. $user->data['master_id'];
					
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					
					$master_name = $row['username'];
					//$error=$user->lang['NOT_MASTER'];
				}
				else
				{
					$master_name = $user->data['username'];
					$is_master=true;
				}
				
				if ($submit != '')
				{
					switch ($submit)
					{
						case $user->lang['SWITCH_USER']:
							$forumid = request_var('switch_acct', 0);
							$redirect = request_var('redirect', $phpbb_root_path);
							
							switch_user($forumid);
						break;
						case $user->lang['LINK_USERS']:
							if ($error=='')
							{
								$data = $error = array();
								$updated = false;
								
								$master_name = $user->data['username'];
								$master_pass = request_var('master_pass', '', true);
								$linked_name = request_var('linked_name', '', true);
								$linked_pass = request_var('linked_pass', '', true);
								$result = link_account_validate ($master_name, $master_pass, $linked_name, $linked_pass);
							
								if ($result===true)
								{
									add_log('user', $user->data['user_id'], $user->data['user_id'], 'LOG_LINK_SUCCESS', $master_name, $linked_name);
									$message = $user->lang['LINK_SUCCESS'];
								}
								else
								{
									$error = $result;
									add_log('user', $user->data['user_id'], 'LOG_LINK_FAILED', $master_name, $linked_name, $error);
								}
							}
						break;
						case $user->lang['UNLINK_USERS']:
							if (empty($error))
							{
								$master_name = $user->data['username'];
								$usernames = request_var('usernames', array(0), true);
								if (is_array($usernames))
								{
									$user_id_ary = array();
									foreach ($usernames as $user_id)
									{
										$result = link_accounts(0, $user_id);
										$message = $user->lang['LINK_BROKEN'];
										$user_id_ary[] = $user_id;
									}
									user_get_id_name($user_id_ary, $username_ary);
									add_log ('user', $user->data['user_id'], 'LOG_LINK_BROKEN', $master_name, implode(', ', $username_ary));
								}
							}
						break;
					}
				}
				
			break;
			case 'moderate':
				$this->page_title = 'Moderate Linked Accounts';
				$this->tpl_name = 'mcp_account_link_moderate';
				
				$username = request_var('username', '');
				
				
				if ($username != '' && !in_array(strtolower($username), $account_link_config['blacklist']))
				{
					$username_ary = array($username);
					user_get_id_name($user_id_ary, $username_ary);
					$user_id = $user_id_ary[0];
					
					$master_account = get_master_account($user_id);
					$master_id = $master_account['id'];
					$master_name = $master_account['username'];
					
					$is_master = true;
					$template->assign_var('USER_ID', $master_id);
				}
				
				if ($submit != '')
				{
					switch ($submit)
					{
						case $user->lang['LINK_USERS']:
							if ($error=='')
							{
								$data = $error = array();
								$updated = false;
								
								$linked_name = request_var('linked_name', '', true);
								
								$username_ary = array($linked_name);
								$user_id_ary = array();
								user_get_id_name($user_id_ary, $username_ary);
								$link_id = $user_id_ary[0];
								//echo '<pre>'.print_r($username_ary, true).'</pre>';
								
								// Let's check a few things for security's sake...
								// Is 'linked_name' linked to any other accounts?
								$linked_accounts = get_linked_accounts($link_id);
								
								$sql = 'SELECT user_type
										FROM ' . USERS_TABLE . '
										WHERE (user_id = '. $link_id .')';
										
								$result = $db->sql_query($sql);
								$row = $db->sql_fetchrow($result);
								
								(int) $user_type = $row['user_type'];
								
								if ($user_type != USER_NORMAL)
								{
									//die('error!');
									$message = sprintf($user->lang['NOT_NORMAL_USER'], $linked_name);
									add_log('user', $user->data['user_id'], 'LOG_LINK_FAILED', $master_name, $linked_name, $message);
								}
								elseif (count($user_id_ary) == 0)
								{
									//die('error!');
									$message = sprintf($user->lang['BAD_LINKED_USERNAME'], $linked_name);
									add_log('user', $user->data['user_id'], 'LOG_LINK_FAILED', $master_name, $linked_name, $message);
								}
								elseif (count($linked_accounts) != 1)
								{
									//die('error!');
									$message = $user->lang['CANNOT_LINK_MASTER'];
									add_log('user', $user->data['user_id'], 'LOG_LINK_FAILED', $master_name, $linked_name, $message);
								}
								else
								{
									//die('Yay!');
									$result = link_accounts ($master_id, $link_id);
							
									if ($result===true)
									{
										add_log('user', $user->data['user_id'], 'LOG_LINK_SUCCESS', $master_name, $linked_name);
										$message = $user->lang['LINK_SUCCESS'];
									}
									else
									{
										$error = $result;
										add_log('user', $user->data['user_id'], 'LOG_LINK_FAILED', $master_name, $linked_name, $error);
									}
								}
							}
						break;
						case $user->lang['UNLINK_USERS']:
							if ($error=='')
							{
								/*
								$unlink_username = request_var('unlink_username', '', true);
								$username_ary = array($unlink_username);
							
								user_get_id_name($user_id_ary, $username_ary);
								
								$unlink_id = $user_id_ary[0];
								*/
							
								$usernames = request_var('usernames', array(0), true);
								if (is_array($usernames))
								{
									$user_id_ary = array();
									foreach ($usernames as $user_id)
									{
										// Master shouldn't change, do let's only grab it once.
										if (empty($master_name))
										{
											list($master_id, $master_name) = get_master_account($user_id);
										}
									
										$result = link_accounts(0, $user_id);
										$message = $user->lang['LINK_BROKEN'];
										$user_id_ary[] = $user_id;
									}
									user_get_id_name($user_id_ary, $username_ary);
									
									add_log ('user', $user->data['user_id'], 'LOG_LINK_BROKEN', $master_name, implode(', ', $username_ary));
								}
							}
						break;
					}
				}
			break;
		}
	
		// Template stuff
		$s_account_link_options ='';
		
		$linked_accts = get_linked_accounts($master_id);
		
		//echo "Master Account: $master_id";
		//echo '<pre' . print_r($linked_accts, true) . '</pre>';
		//die();
		
		foreach($linked_accts as $key => $value)
		{
			if ($key != $master_id && !in_array(strtolower($value), $account_link_config['blacklist']))
			{
				$s_account_link_options .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		
		if (!empty($error) )
		{
			$template->assign_var('ERROR', $error);
		}
		
		if (!empty($message) )
		{
			$template->assign_var('MESSAGE', $message);
		}
		
		$template->assign_vars(array( 
			'TITLE'					=> 'Linked Accounts',
			
			'IS_MASTER_ACCOUNT'		=> $is_master,
			'L_NOT_MASTER'			=> sprintf($user->lang['NOT_MASTER'],  $master_name),
			
			'U_THIS_URL'			=> $_SERVER['REQUEST_URI'],
			'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=searchuser&amp;form=select_user&amp;field=username&amp;select_single=true"),
			
			'S_MCP_ACCOUNT_LINK_OPTIONS'=> $s_account_link_options,
			'S_MASTER_NAME'			=> $master_name,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action)
		);
	}
}

?>