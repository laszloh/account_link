<?php
/**
*
* @author Michael Flenniken, Jr drakkim@conclavewiz.com
*
* @package ucp
* @version SVN: $Id: ucp_account_link.php 77 2012-09-02 10:48:53Z drakkim $
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

/**
* @package ucp
*/
class ucp_account_link
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
		
		$u_redirect = request_var('redirect', "{$phpbb_root_path}index.$phpEx");
		$l_redirect = ($u_redirect === ("{$phpbb_root_path}index.$phpEx" || $u_redirect === "index.$phpEx")) ? $user->lang['RETURN_INDEX'] : $user->lang['RETURN_PAGE'];
		
		// append/replace SID (may change during the session for AOL users)
		$u_redirect = reapply_sid($u_redirect);
		$redirect = '<br /><br />' . sprintf($l_redirect, '<a href="' . $u_redirect . '">', '</a>');
		
		$s_hidden_fields = '';
		$error='';
//		$error = request_var('error','');
		
		switch ($mode)
		{
			case 'manage':
				$this->page_title = 'Manage Linked Accounts';
				$this->tpl_name = 'ucp_account_link';
				break;
			case 'create_join':
				get_all_groups();
			case 'create':
				if ($account_link_config['group_enabled'])
				{
					get_all_groups();
				}
				$this->page_title = 'Create Linked Account';
				$this->tpl_name = 'ucp_account_link_create';
				break;
		}
		
		// Always need this
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
					if (empty($error))
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
							add_log('user', 'LOG_LINK_SUCCESS', $master_name, $linked_name);
							$message = $user->lang['LINK_SUCCESS'];
						}
						else
						{
							$error = $result;
							add_log('user', 'LOG_LINK_FAILED', $master_name, $linked_name, $error);
						}
					}
				break;
					case $user->lang['UNLINK_USERS']:
						if (empty($error))
						{
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
				/**
				* @todo generate profile override list
				* @todo figure out groups / default group
				*/
				case $user->lang['CREATE_LINKED_ACCOUNT']:
					if (empty($error))
					{
						$data = $error = array();
						$updated = false;
						
						$master_name = $user->data['username'];
						$master_pass = request_var('master_pass', '', true);
						$linked_name = request_var('linked_name', '', true);
						$linked_pass = request_var('linked_pass', '', true);
						$confirm_pass = request_var('confirm_pass', '', true);
						$default_group_id = request_var('main_group', 0);
						$user_groups = request_var('user_groups', array());
						
						if ($linked_pass != $confirm_pass)
						{
							$error[] = $user->lang('NEW_PASSWORD_ERROR');
							
						//	meta_refresh(3, $u_redirect);
						//	trigger_error(implode(', ', $error) . $redirect);
						}
						elseif (($pass_valid = validate_password($linked_pass)) !== false)
						{
							$error[] = $user->lang($pass_valid);
							
						//	meta_refresh(3, $u_redirect);
						//	trigger_error(implode(', ', $error) . $redirect);
						}
						/**
						* This block makes choosing a group mandatory. Uncomment it to make it work!
						*/
						/*
						if ($default_group_id==0 && $mode == 'create_join')
						{
							$error[] = 'No group chosen';
							
							//meta_refresh(3, $u_redirect);
							//trigger_error(implode(', ', $error) . $redirect);
						}
						/* */
						// Make sure $default_group_id is set
						if ($account_link_config['group_required'] && empty($default_group_id))
						{
							$error[] = $user->lang('LINKED_GROUP_REQUIRED');
						}
						
						// Make sure $default_group_id is in $user_groups
						if (empty($user_groups) && $default_group_id != 0)
						{
							$user_groups = array($default_group_id=>'');
						}
						//elseif (!array_key_exists($default_group_id, $user_groups))
						{
						//	$user_groups = array_merge(array("$default_group_id"=>0), $user_groups);
						}
											
						$new_user=array(
							'username'		=> $linked_name,
							'user_password'	=> phpbb_hash($linked_pass),
							'group_id'		=> $default_group_id
						);

						if (empty($error))
						{
							$result = create_linked_account ($master_name, $master_pass, $new_user, $user_groups);
							
							//$result = link_account_validate ($master_name, $master_pass, $linked_name, $linked_pass);
						
							if ($result===true)
							{
								$message = $user->lang['LINK_SUCCESS'];
							}
							else
							{
								$error[] = $result;
							}
						}
						else
						{
							//meta_refresh(3, $u_redirect);
							trigger_error(implode(', ', $error));
						}
					}
				break;
			}
		}
		elseif (($forumid = request_var('switch_acct', 0)) !=0)
		{
			$redirect = request_var('redirect', $phpbb_root_path);
			
			switch_user($forumid);
		}
		$s_account_link_options ='';
		
		$linked_accts = get_linked_accounts();
		foreach($linked_accts as $key => $value)
		{
			if ($key != $user->data['user_id'])
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

			'S_ACCOUNT_LINK_OPTIONS'=> $s_account_link_options,
			'S_MASTER_NAME'			=> $master_name,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> $this->u_action)
		);
	}
}

?>