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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package ucp
*/
class ucp_account_link
{
	var $u_action;

	function main($id, $mode)
	{
		global $user, $template, $phpbb_root_path, $phpEx, $db;
		global $alink;
		
		$user->add_lang('mods/account_link');
		
		$submit = request_var('submit', false);
		$action = request_var('action', '', true);
		
		$u_redirect = request_var('redirect', "{$phpbb_root_path}index.$phpEx");
		$l_redirect = ($u_redirect === ("{$phpbb_root_path}index.$phpEx" || $u_redirect === "index.$phpEx")) ? $user->lang['RETURN_INDEX'] : $user->lang['RETURN_PAGE'];
		
		// append/replace SID (may change during the session for AOL users)
		$u_redirect = reapply_sid($u_redirect);
		$redirect = '<br /><br />' . sprintf($l_redirect, '<a href="' . $u_redirect . '">', '</a>');
		
		$error = array();
		
		// Always need this
		$is_master = false;
		if ($user->data['master_id'] != 0)
		{
			$sql = 'SELECT username
			FROM ' . USERS_TABLE . '
				WHERE user_id = '. $user->data['master_id'];
			
			$result = $db->sql_query($sql);
			$master_name = $db->sql_fetchfield('username');
			$db->sql_freeresult($result);
		}
		else
		{
			$master_name = $user->data['username'];
			$is_master = true;
		}
		
		// first always validiate hidden fileds (when we get a submit)
		if($submit)
		{
			if(!check_form_key('ucp_alink'))
			{
				$error[] = $user->lang['FORM_INVALID'];
			}
		}
		
		switch ($mode)
		{
			case 'manage':
				$this->page_title = $user->lang['MANAGE_LINKED_ACCOUNTS'];
				$this->tpl_name = 'ucp_alink_manage';
				
				if($submit && $is_master && !sizeof($error))
				{
					switch($action)
					{
						case 'link':
							$master_pass = request_var('master_pass', '', true);
							$linked_name = request_var('linked_name', '', true);
							$linked_pass = request_var('linked_pass', '', true);
							
							$result = $alink->link_account_validate ($master_name, $master_pass, $linked_name, $linked_pass);
						
							if ($result === true)
							{
								add_log('user', 'LOG_LINK_SUCCESS', $master_name, $linked_name);
								$message = $user->lang['LINK_SUCCESS'];
							}
							else
							{
								$error = $result;
								add_log('user', 'LOG_LINK_FAILED', $master_name, $linked_name, $error);
							}
							break;

						case 'unlink':
							$user_id = request_var('uid', 0);
							user_get_id_name(array($user_id), $username_ary);

							// confirm the unlink
							if(confirm_box(true))
							{
								// test if accounts are linked
								if(!$alink->is_account_linked($user->data['user_id'], $unlink_id))
								{
									$error = $user->lang['ACCOUNT_UNLINK_FAILED'];
									add_log('user', 'LOG_ACCOUNT_UNLINK_FAILED', $master_name, implode(' ', $username_ary));
									// hammer time!
									trigger_error($error);
								}
							
								// do the unlink
								$alink->unlink_accounts($user->data['user_id'], $unlink_id);
								$message = $user->lang['LINK_BROKEN'];
								add_log ('user', $user->data['user_id'], 'LOG_LINK_BROKEN', $master_name, implode(' ', $username_ary));
							}
							else
							{
								confirm_box(false, sprintf($user->lang['CONFIRM_ACCOUNT_UNLINK', implode(', ', $username_ary)), build_hidden_fields(array(
									'mode'		=> 'manage',
									'action'	=> 'unlink',
									'submit'	=> true,
									'uid'		=> $user_id,
									)));
							}
						
							break;
					}
				}
				
				break;
				
			case 'switch':
				$this->page_title = $user->lang['SWITCH_LINKED_ACCOUNTS'];
				$this->tpl_name = 'ucp_alink_switch';
				break;

			case 'config':
				$this->page_title = $user->lang['CONFIG_LINKED_ACCOUNTS'];
				$this->tpl_name = 'ucp_alink_config';
				
				// get settings
				$alink->get_user_config_tmplt();

				if($submit && $is_master && !sizeof($error))
				{
					// save settings
					$settings = $alink->get_settings_var();
					
					$result = $alink->save_user_config($settings);
					
					// we are done generate output
					meta_refresh(3, $this->u_action);
					if ($result === true)
					{
						trigger_error('ALINK_SETTINGS_SAVED' . $redirect);
					}
					else
					{
						trigger_error('ACCT_SAVE_ERROR' . $redirect);
					}
				}
			break;
			
			case 'create':
				if(!$user->acl_get('u_alink_create'))
				{
					$error = $user->lang['ALINK_CREATE_FAILED'];
					add_log('user', 'LOG_ALINK_CREATE_FAILED', $master_name);
					// hammer time!
					trigger_error($error);
				}
				$this->page_title = 'Create Linked Account';
				$this->tpl_name = 'ucp_alink_create';
				
				// not implemented
				$error = $user->lang['ACCOUNT_CREATE_MISSING'];
				add_log('user', 'LOG_ACCOUNT_CREATE_MISSING', $user->data['username'], implode(' ', $username_ary));
				// hammer time!
				trigger_error($error);
				
				break;
		}
		
		add_form_key('ucp_alink');
		
		// fill up the global template variable
		$template->assign_vars(array( 
			'TITLE'					=> $user->lang['ACCOUNT_LINK'],
			
			'IS_MASTER_ACCOUNT'		=> $is_master,
			'L_NOT_MASTER'			=> sprintf($user->lang['NOT_MASTER'],  $master_name),
			'S_MASTER_NAME'			=> $master_name,
			
			'S_UCP_ACTION'			=> $this->u_action)
		);
		
		// add the data of the linked accounts
		$alink->get_linked_accounts_tmplt();
	}
}

?>