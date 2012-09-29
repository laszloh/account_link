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
		global $user, $auth, $template, $phpbb_root_path, $phpEx, $db;
		global $alink;
		$user->add_lang('mods/account_link');
		
		$submit = request_var('submit', '', true);
		$action = request_var('action', '', true);
		
		$u_redirect = request_var('redirect', "{$phpbb_root_path}index.$phpEx");
		$l_redirect = ($u_redirect === ("{$phpbb_root_path}index.$phpEx" || $u_redirect === "index.$phpEx")) ? $user->lang['RETURN_INDEX'] : $user->lang['RETURN_PAGE'];
		
		// append/replace SID (may change during the session for AOL users)
		$u_redirect = reapply_sid($u_redirect);
		$redirect = '<br /><br />' . sprintf($l_redirect, '<a href="' . $u_redirect . '">', '</a>');
		
		$error = $message = array();
		
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
		
		switch ($mode)
		{
			case 'manage':
				$this->page_title = $user->lang['MANAGE_LINKED_ACCOUNTS'];
				$this->tpl_name = 'ucp_alink_manage';
							
				if($is_master)
				{
					switch($submit)
					{
						case 'link':
							if(!check_form_key('ucp_alink'))
							{
								$error[] = $user->lang['FORM_INVALID'];
								break;
							}
							$master_pass = request_var('master_pass', '', true);
							$linked_name = request_var('linked_name', '', true);
							$linked_pass = request_var('linked_pass', '', true);
							
							$result = $alink->link_account_validate ($master_name, $master_pass, $linked_name, $linked_pass);
						
							if ($result === true)
							{
								add_log('user', 'LOG_LINK_SUCCESS', $master_name, $linked_name);
								$message[] = $user->lang['LINK_SUCCESS'];
							}
							else
							{
								$error = array_merge($error, $result);
								add_log('user', 'LOG_LINK_FAILED', $master_name, $linked_name, $error);
							}
							break;

						case 'unlink':
							$uids = request_var('userids', array(0));
							user_get_id_name($uids, $username_ary);


							// confirm the unlink
							if(confirm_box(true))
							{
								$unlink_id = $uids[0];
							
								// test if accounts are linked
								if(!$alink->is_account_linked($user->data['user_id'], $unlink_id))
								{
									$error = $user->lang['ACCOUNT_UNLINK_FAILED'];
									add_log('user', 'LOG_LINK_BROCKEN_FAILED', $master_name, implode(' ', $username_ary));
									// hammer time!
									trigger_error($error);
								}

								// do the unlink
								$alink->unlink_accounts($unlink_id, $user->data['user_id']);
								$message[] = $user->lang['LINK_BROKEN'];
								add_log ('user', $user->data['user_id'], 'LOG_LINK_BROKEN', $master_name, implode(' ', $username_ary));
							}
							else
							{
								confirm_box(false, sprintf($user->lang['CONFIRM_ACCOUNT_UNLINK'], implode(' ', $username_ary), $master_name), build_hidden_fields(array(
									'mode'		=> 'manage',
									'submit'	=> 'unlink',
									'userids'	=> $uids,
									)));
							}
							break;
							
						case 'config':
							$data = array(
								'alink_position'	=> request_var('disp_pos', 1),
								'alink_hidespeci'	=> request_var('hide_acc', 0),
								'alink_pm_mode'		=> request_var('multi_pn', 1),
							);
							$validate_array = array(
								'alink_position'	=> array('num', true, 0, 2),
								'alink_pm_mode'		=> array('num', true, 0, 1),
							);
							$error = validate_data($data, $validate_array);
							
							if(!check_form_key('ucp_alink'))
							{
								$error[] = $user->lang['FORM_INVALID'];
							}
							
							if(!sizeof($error))
							{
								// save the settings
								$sql = 'UPDATE ' . USERS_TABLE . '
									SET ' . $db->sql_build_array('UPDATE', $data) . '
									WHERE user_id = ' . $user->data['user_id'];
								$db->sql_query($sql);
								
								meta_refresh(3, $this->u_action);
								$message = $user->lang['CONFIG_ALINK_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
								trigger_error($message);
							}
							
							break;
					}
				}
				
				break;
				
			case 'switch':
				$this->page_title = $user->lang['SWITCH_LINKED_ACCOUNTS'];
				$this->tpl_name = 'ucp_alink_switch';
				break;

			case 'create':
				if(!$auth->acl_get('u_alink_create'))
				{
					$error = $user->lang['ALINK_CREATE_FAILED'];
					add_log('user', 'LOG_ALINK_CREATE_FAILED', $master_name);
					// hammer time!
					trigger_error($error . $this->u_action);
				}
				$this->page_title = 'Create Linked Account';
				$this->tpl_name = 'ucp_alink_create';
				
				// not implemented
				$error = $user->lang['ACCOUNT_CREATE_MISSING'];
				add_log('user', 'LOG_ACCOUNT_CREATE_MISSING', $user->data['username'], implode(' ', $username_ary));
				// hammer time!
				trigger_error($error . $this->u_action);
				
				break;
		}
		
		add_form_key('ucp_alink');
		
		// fill up the global template variable
		$template->assign_vars(array( 
			'TITLE'				=> $user->lang['ACCOUNT_LINK'],
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'MESSAGE'			=> (sizeof($message)) ? implode('<br />', $error) : '',
			
			'IS_MASTER_ACCOUNT'	=> $is_master,
			'L_NOT_MASTER'		=> sprintf($user->lang['NOT_MASTER'],  $master_name),
			'S_MASTER_NAME'		=> $master_name,
			
			'S_ALINK_POSITION'	=> $user->data['alink_position'],
			'S_ALINK_HIDE'		=> ($user->data['alink_hidespeci']) ? true : false,
			'S_ALINK_PM_MODE'	=> ($user->data['alink_pm_mode']) ? true : false,
			
			'S_UCP_ACTION'		=> ($mode=='switch') ? "{$phpbb_root_path}alink.{$phpEx}" : $this->u_action,
		));
	}
}

?>