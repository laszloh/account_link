<?php
/**
* @author Michael Flenniken, Jr drakkim@conclavewiz.com
*
* @package phpBB
* @version SVN: $Id: functions_account_link.php 77 2012-09-02 10:48:53Z drakkim $
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

// The main class
class alink 
{

	/**
	 * Initializes the alink class, fetches the configuration and set's up the 
	 * langaue files for the account link modul
	 */
	function alink()
	{
		$user->add_lang('mod/account_link');
	}
	
	/**
	 * Fills out the template variable with the linked account informations.
	 * 
	 * @param $user_id	The user id to be queried. If 0 the current user will be taken
	 */
	function get_linked_accounts_tmplt ($user_id = 0)
	{
		global $user, $phpbb_root_path, $phpEx, $template;
		
		$linked_accts = $this->get_linked_accounts($user_id);		
		$user_id = ($user_id == 0) ? $user->data['user_id'] : $user_id;
		
		$new_mails = $unread_mails = 0;
		foreach($linked_accts as $linked)
		{
			if ($linked['user_id'] != $user_id)
			{
				$template->assign_block_vars('linked', array(
					'USER_ID'		=> $linked['user_id'],
					'USERNAME'		=> $linked['username_clean'],
					'USER_NAME'		=> get_username_string('no_profile', $linked['user_id'], $linked['username'], $linked['user_colour']),
					'USER_PROFILE'	=> get_username_string('full', $linked['user_id'], $linked['username'], $linked['user_colour']),,
					
					'NEW_PM'		=> $linked['user_new_privmsg'],
					'UNREAD_PM'		=> $linked['user_unread_privmsg'],
					
					'FORM_NAME'		=> 'userid_' . $linked['user_id'],
					'HIDDEN_FIELDS'	=> build_hidden_fields(array(
						'submit' 	=> 'switch',
						'switch_id'	=> $linked['user_id'],
						'redirect'	=> build_url(),
						)),
				));
			}
			$new_mails += $linked['user_new_privmsg'];
			$unread_mails += $linked['user_unread_privmsg'],
		}
		
		$template->assign_vars(array(
			'ALINK_NEW_MAILS'				=> $new_mails,
			'ALINK_UNREAD_MAILS'			=> $unread_mails,
			
			'U_ACCOUNT_LINK_SEND_TO'	=> append_sid($phpbb_root_path ."ucp.$phpEx?i=account_link"),
		) );
	}
	
	/**
	 * Retrieve all linked account rows for $user_id
	 *
	 * @param $user_id	The user id to be queried. If 0 the current user will be taken
	 * @return an array containing the user rows of all linked accounts, including the queried account
	 */
	function get_linked_accounts ($user_id = 0)
	{
		global $db, $user, $auth;

		if ($user->data['user_id'] == ANONYMOUS && $user_id == 0)
		{
			return array();
		}

		$user_id = ($user_id == 0) ? $user->data['user_id'] : $user_id;
		$master_id = $this->get_master_account($user_id);

		$sql_array = array(
			'SELECT'	=> 'u.*',
			'FROM'		=> USERS_TABLE . ' AS u',
			'WHERE'		=> '(user_id = ' . $user_id . ')
							OR (master_id = ' . $master_id : ')',
			);

		// Look up all users with user_id or master_id matching $user_id or $master_id
		if ($master_id != 0)
		{
			$sql_array['WHERE'] .= ' OR (user_id = '. $master_id .')
							   OR (master_id = '. $master_id .')';
		}
		
		$result = $db->sql_query($db->sql_build_query('SELECT', $sql_array));
		$linked_accounts = $db->fetchrowset($result);
		$db->sql_freeresult($result);
		
		return $linked_accounts;
	}
	
	/*
	 * Returns the user_id of the master account
	 * 
	 * @param $user_id	The id of the user to be queried. If 0 the current user will be used
	 * @return The queried user id of the master account
	 */
	function get_master_account ($user_id = 0)
	{
		global $db, $user;
		
		if ($user_id == 0)
		{
			if ($user->data['user_id'] == ANONYMOUS)
			{
				return false;
			}
			$user_id = $user->data['user_id'];
			$master_id = $user->data['master_id'];
		}
		else
		{
			// Look up master_id from USER_TABLE
			$sql = 'SELECT master_id
				FROM ' . USERS_TABLE . '
				WHERE (user_id = '. $user_id .')';
					
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			$master_id = (int) $row['master_id'];
		}
		
		return ($master_id == 0) ? $user_id : $master_id;
	}
	
	/**
	* Links accounts by username without validation.
	* Actually wraps link_accounts()
	* 
	* @warning No Validation
	* @deprecated
	*
	* @param $master_username	The username of the master account
	* @param $linked_username	The username of the account to be linked

	*/
	function link_accounts_by_name ($master_username = false, $linked_username = false)
	{
		if($master_username === false || $linked_username === false)
		{
			return false;
		}
	
		$user_id_ary = array();
		$username_ary = array (utf8_clean_string($master_username), utf8_clean_string($linked_username));
		user_get_id_name ($user_id_ary, $username_ary);
		$master_id = $user_id_ary[0];
		$linked_id = $user_id_ary[1];

		return $this->_link_accounts($master_id, $linked_id);
	}
	
	/**
	* Links the accounts together without validation by setting the 
	* 'master_id' for user with 'user_id' = $linked_id.
	*
	* @warning No Validation
	*
	* @param $master_id	The user_id of the master account
	* @param $linked_id	The id of the account to be linked
	*/
	function _link_accounts($master_id = 0, $linked_id = 0)
	{
		global $db;
		
		if($master_id == 0 || $linked_id == 0)
		{
			return false;
		}

		$sql = 'UPDATE '. USERS_TABLE 
				.' SET master_id = '. $master_id 
				.' WHERE user_id = '. $linked_id;
				
		// Need to check for errors....
		$db->sql_query($sql);
		return true;
	}
	
	/**
	* Break the Account Link between two linked accounts. Takes into 
	* consideration the case, when the master account is unlinked. If this 
	* happens, the current user account becomes the master account.
	*
	* @warning No Validation
	*
	* @param $link_id	the user_id of the linked account
	* @param $master_id	the id of the master account
	*/
	function unlink_accounts ($link_id = 0, $master_id = 0)
	{
		global $db, $user;
		
		if($link_id == 0 || $master_id == 0)
		{
			return false;
		}
		
		$sql = 'SELECT user_id, master_id 
			FROM ' . USERS_TABLE . ' 
			WHERE user_id = ' . $link_id;
		$result = $db->sql_query($sql);
		$link_user = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if($link_user['user_id'] == $master_id)
		{
			// this account will become the master account, so update all linked accounts with the new master_id
			$sql = 'UPDATE ' . USERS_TABLE . ' 
				SET master_id = ' . $user->data['user_id'] . ' 
				WHERE master_id = ' . $master_id;
			$db->sql_query($sql);
			
			// delete master_id so it shows as an account master
			$sql = 'UPDATE '. USERS_TABLE .'
					SET master_id = 0
					WHERE user_id = '. $user->data['user_id'];
		}
		else
		{
			// remove account from link
			$sql = 'UPDATE '. USERS_TABLE .'
					SET master_id = 0
					WHERE user_id = '. link_user['user_id'];
		}
		
		$db->sql_query($sql);
		return true;
	}
	
	/**
	* Check the usernames and passwords for both the master account and the one we're linking to then links the accounts
	* Validates $master_pass, $linked_username, and $linked_pass
	*/
	function link_account_validate ($master_username, $master_pass, $linked_username, $linked_pass)
	{
		global $db, $user;
		
		// Clean usernames
		$linked_username = utf8_clean_string($linked_username);
		$master_username = utf8_clean_string($master_username);
		
		$error = array();
		
		// Retrieve both users
		$sql = 'SELECT user_id, user_password, username_clean'.
			' FROM ' . USERS_TABLE .
			' WHERE (username_clean = \''. $db->sql_escape($master_username) .'\')';
		$result = $db->sql_query($sql);
		$master_user = $db->sql_fetchrow($result);
		
		$sql = 'SELECT user_id, user_password, username_clean'.
			' FROM ' . USERS_TABLE .
			' WHERE (username_clean = \''. $db->sql_escape($linked_username) .'\')';
		$result = $db->sql_query($sql);
		$linked_user = $db->sql_fetchrow($result);
		
		// Validate users...
		$invalid = false;

		// Does $linked_user exist?
		if ($linked_user['username_clean'] != $linked_username)
		{
			$invalid = true;
			$error[] = sprintf($user->lang['BAD_LINKED_USERNAME'], $linked_username);
		}
		
		// Check Master's password
		if (!phpbb_check_hash($master_pass, $master_user['user_password']))
		{
			$invalid = true;
			$error[] = $user->lang['BAD_MASTER_PASSWORD'];
		}
		
		// Check Linked's password
		if (!phpbb_check_hash($linked_pass, $linked_user['user_password']))
		{
			$invalid = true;
			$error[] = $user->lang['BAD_LINKED_PASSWORD'];
		}
		
		// Destroy plaintext passwords... just in case.
		unset($master_pass, $linked_pass);
		
		if ($invalid === false)
		{
			return link_accounts($master_user['user_id'], $linked_user['user_id']);
		} 
		else 
		{
			return implode('<br>', $error);
		}
	}
	
	/**
	 * Checks if two accounts are linked together
	 *
	 * @param $user_id	The first user id. If 0 the current user will be taken
	 * @param $test_id	The id of the second user. Can <b>not</b> be 0
	 * @return true if the two accounts are linked, false otherwise
	 */
	function is_account_linked($user_id = 0, $test_id = 0)
	{
		$user_id = ($user_id == 0) ? $user->data['user_id'] : $user_id;
		
		if ($user_id == ANONYMOUS || $test_id == 0)
		{
			return false;
		}
		
		$accounts = $this->get_linked_accounts($user_id);
		foreach($account as i => $linked)
		{
			// test if we found the value
			if($linked['user_id'] == $test_id)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Synchronize the uread forum/topic marks of two users
	 * Not a basic funtion, it will test, if users are linked together, 
	 * before executing the synchronization
	 *
	 * @param $dest_user	user_id of the user, which should be overwritten
	 * @param $source_user	user_id of the user supplying the data
	 */
	function sync_unread_topics($dest_user = 0, $source_user = 0)
	{
		// not implemented
	}
	
}

/**
* Get Account Link config
*/
function account_link_config()
{
	global $phpbb_root_path;
	
	require($phpbb_root_path . '/includes/account_link_cfg.php');
	
	return $account_link_config;
}


/**
* Create a new account that is linked to the current one (or it's master)
*
* @param string $master_username username of master account
* @param string $master_pass master account's password
* @param array $new_user array of overrides for the new user account
* @param array $user_groups group numbers to join (array of  group_id => (bool) approved (true=approved / false=default) ) The first group will be set as primary.
*/
function create_linked_account ($master_username, $master_pass, $new_user=array(), $user_groups=array())
{
	global $db, $user, $config, $data, $auth;
	global $phpbb_root_path, $phpEx;
	
	$u_redirect = request_var('redirect', "{$phpbb_root_path}index.$phpEx");
	$l_redirect = ($u_redirect === ("{$phpbb_root_path}index.$phpEx" || $u_redirect === "index.$phpEx")) ? $user->lang['RETURN_INDEX'] : $user->lang['RETURN_PAGE'];
	
	// append/replace SID (may change during the session for AOL users)
	$u_redirect = reapply_sid($u_redirect);
	$redirect = '<br /><br />' . sprintf($l_redirect, '<a href="' . $u_redirect . '">', '</a>');
	
	// Make sure $new_user has _at least_ thew bare minimum info (username, etc.)
	if (!isset($new_user['username']) || !isset($new_user['user_password']))
	{
		// Error! we need more info!
		$message = $user->lang['NEW_INFO_REQUIRED'];
		meta_refresh(3, $u_redirect);
		trigger_error($message . $redirect);
	}
	
	// Set some defaults... reset permissions, remove founder, etc...
	$user_defaults = array(
		'user_regdate'	=> time(),
	);
	
	$group_defaults = array(
	
	);
	
	// Clean username
	$master_username = utf8_clean_string($master_username);
	
	$error = array();
	
	// Retrieve master user (user_id, password, and username with no special chars)
	//$sql = 'SELECT *
	$sql = 'SELECT user_id, user_password, username_clean, master_id, user_ip, user_email,
			user_email_hash, user_lang, user_timezone, user_dst
		FROM ' . USERS_TABLE . '
		WHERE (username_clean = "'. $master_username .'")';
	$result = $db->sql_query($sql);
	$master_user = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	// Is the master account a master account?
	if ($master_user['master_id'] != 0)
	// Nope! We need to tell the user he has to use the master account!
	{
		// Destroy plaintext passwords... just in case.
		unset($master_pass);
		
		$sql = 'SELECT username
		FROM ' . USERS_TABLE . '
			WHERE user_id = '. $master_user['master_id'];
		
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		
		$master_name = $row['username'];
	
		$message = sprintf($user->lang['NOT_MASTER'],  $master_name);
		meta_refresh(3, $u_redirect);
		trigger_error($message . $redirect);
	}

	// Check Master's password, If the account validates, copy what we need
	if (phpbb_check_hash($master_pass, $master_user['user_password']))
	{
		// Unset a number of options
		// Destroy plaintext passwords... just in case.
		unset($master_pass);
		
		$message = array();
		
		$old_user = array(
			'user_ip'			=> $master_user['user_ip'],
			'user_email'		=> $master_user['user_email'],
			'user_email_hash'	=> $master_user['user_email_hash'],
			'user_lang'			=> $master_user['user_lang'],
			'user_timezone'		=> $master_user['user_timezone'],
			'user_dst'			=> $master_user['user_dst'],
		);
		
		/**
		* based on acp_add_user mod by David Lewis (Highway of Life)
		* Set activation stuff
		*
		* We're auto-activating on USER_ACTIVATION_SELF since the email's already been validated (copied from master account)
		*/
		$server_url = generate_board_url();
		
		
		/* Remove activation
		//if (($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'] && !$admin_activate)
		if (($config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable']) // && !$admin_activate)
		{
			$user_actkey = gen_rand_string(10);
			$key_len = 54 - (strlen($server_url));
			$key_len = ($key_len < 6) ? 6 : $key_len;
			$new_user['user_actkey'] = substr($user_actkey, 0, $key_len);

			$new_user['user_type'] = USER_INACTIVE;
			$new_user['user_inactive_reason'] = INACTIVE_REGISTER;
			$new_user['user_inactive_time'] = time();
		}
		else
		*/
		{
			$new_user['user_type'] = USER_NORMAL;
			$new_user['user_actkey'] = '';
			$new_user['user_inactive_reason'] = 0;
			$new_user['user_inactive_time'] = 0;
		}
		
$group_name = 'REGISTERED';
$sql = 'SELECT group_id
        FROM ' . GROUPS_TABLE . "
        WHERE group_name = '" . $db->sql_escape($group_name) . "'
            AND group_type = " . GROUP_SPECIAL;
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$new_user['group_id'] = $row['group_id'];
		
		
		
		$new_user = array_merge($old_user, $user_defaults, $new_user);
		unset($new_user['user_id']);
		
		// Does the user exist already?
		if ( ($user_valid = validate_username($new_user['username']))!==false)
		{
			//$error[] = $user_valid;
			trigger_error($user_valid, E_USER_ERROR);
		}
		else
		{		
			// Register user...
			$user_id = user_add($new_user);
			if ($user_id===false)
			{
				$error[]='User register failed';
				meta_refresh(3, $u_redirect);
				trigger_error($user->lang['NO_USER'] . $redirect);
			}
			
			// Need to fix this....
			//add_log('user', 'LOG_USER_ADDED', $new_user['new_username']);
				
				

//				if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
//				{
//					$message[] = $user->lang['ACCOUNT_INACTIVE'];
//					$email_template = 'user_welcome_inactive';
//				}
//				else
				if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable']) // && !$admin_activate)
				{
					//$message[] = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
					$email_template = 'admin_welcome_inactive';
				}
//				else
//				{
//					$message[] = $user->lang['ACCOUNT_ADDED'];
//					$email_template = 'user_welcome';
//				}


				if ($config['email_enable'])
				{
					include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

					$messenger = new messenger(false);
/* Don't need to send an email... *
					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['new_username']);

					$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
					$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

					$messenger->assign_vars(array(
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['new_username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					$messenger->send(NOTIFY_EMAIL);
/* */
/* Remove activation *
					if ($config['require_activation'] == USER_ACTIVATION_ADMIN) // && !$admin_activate)
					{
						// Grab an array of user_id's with a_user permissions ... these users can activate a user
						$admin_ary = $auth->acl_get_list(false, 'a_user', false);
						$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

						// Also include founders
						$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

						if (sizeof($admin_ary))
						{
							$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
						}

						$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
							FROM ' . USERS_TABLE . ' ' .
							$where_sql;
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$messenger->template('admin_activate', $row['user_lang']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars(array(
								'USERNAME'			=> htmlspecialchars_decode($data['new_username']),
								'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&amp;u=$user_id",
								'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send($row['user_notify_type']);
						}
						$db->sql_freeresult($result);
						
						$message[] = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
					}
					else
*/
					{
						$message[] = $user->lang['ACCOUNT_ADDED'];
					}
				}
			
			// Sign up for groups
			$groups_pending=array();
			
			if (!empty($user_groups) && empty($error))
			{
				$default=true;
				foreach ($user_groups as $group_id => $approved)
				{
					$sql = 'SELECT * FROM ' . GROUPS_TABLE . '
						WHERE group_id = ' . $group_id;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					
					switch ($row['group_type'])
					{
						case GROUP_FREE:
							group_user_add($group_id, $user_id);
							$groups_joined[] = $row['group_name'];
							//$message[] = sprintf($user->lang['LINKED_GROUP_JOINED'], implode(', ', $groups_joined));
							//$message[] = "Added user ($user_id) to group ($group_id) / ".$row['group_type']. ' status: approved';
							break;
						case GROUP_OPEN:
						case GROUP_CLOSED:
						case GROUP_HIDDEN:
							group_user_add($group_id, $user_id, false, false, false, 0, 1);
							$groups_pending[] = $row['group_name'];
							//$message[] = sprintf($user->lang['LINKED_GROUP_JOINED_PENDING'], implode(', ', $groups_pending));
							//$message[] = "Added user ($user_id) to group ($group_id) / ".$row['group_type']. ' status: pending';
							break;
					}
					/*
					if ($row['group_type']==GROUP_FREE)
					// User can freely join
					{
						group_user_add($group_id, $user_id);
					}
					elseif ($row['group_type']==GROUP_OPEN || $row['group_type']==GROUP_CLOSED  || $row['group_type']==GROUP_HIDDEN)
					// User must request membership
					{
						group_user_add($group_id, $user_id, false, false, false, 0, 1);
					}
					*/
					/*if ($default)
					{
						group_user_attributes('default', $group_id, $user_id);
						$default_group_name=$row['group_name'];
						$default = false;
					}
					*/
				}
				if (!empty($groups_joined))
				{
					$message[] = sprintf($user->lang['LINKED_GROUP_JOINED'], implode(', ', $groups_joined));
				}
				if (!empty($groups_pending))
				{
					$message[] = sprintf($user->lang['LINKED_GROUP_JOINED_PENDING'], implode(', ', $groups_pending));
				}
			}
			$linked = link_accounts($master_user['user_id'], $user_id);
			
			if ($linked===true)
			{
				$message[] = sprintf($user->lang['LINK_SUCCESS'], $master_username, $new_user['username']);
			}
			else
			{
				$message[] = $linked;
			}
		}
	}
	else
	{
		$error[] = $user->lang['BAD_MASTER_PASSWORD'];
	}

	
	if (empty($error))
	{
		meta_refresh(3, $u_redirect);
		trigger_error (implode('<br />', $message) . $redirect);
	} else {
		meta_refresh(3, $u_redirect);
		trigger_error (implode('<br />', $error) . $redirect);
	}
}

/**
* List all availible groups
* @todo hide certain groups that aren't needed (Registered, etc)
*/
function get_all_groups ($show_member_groups = false, $hide_groups='' )
{
	global $config, $phpbb_root_path, $phpEx;
	global $db, $user, $auth, $cache, $template;
	
	$user->add_lang('groups');
	
	$group_id_ary = array();
	if ($show_member_groups)
	{
		$sql = 'SELECT g.*, ug.group_leader, ug.user_pending
			FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
			WHERE ug.user_id = ' . $user->data['user_id'] . '
				AND g.group_id = ug.group_id
				AND g.group_type <> ' . GROUP_SPECIAL . '
			ORDER BY g.group_type DESC, g.group_name';
		$result = $db->sql_query($sql);

		$leader_count = $member_count = $pending_count = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$block = ($row['group_leader']) ? 'leader' : (($row['user_pending']) ? 'pending' : 'member');

			switch ($row['group_type'])
			{
				case GROUP_OPEN:
					$group_status = 'OPEN';
				break;

				case GROUP_CLOSED:
					$group_status = 'CLOSED';
				break;

				case GROUP_HIDDEN:
					$group_status = 'HIDDEN';
				break;

				case GROUP_SPECIAL:
					$group_status = 'SPECIAL';
				break;

				case GROUP_FREE:
					$group_status = 'FREE';
				break;
			}

			if ($block != 'pending')
			{
				$template->assign_block_vars('groups', array(
					'GROUP_ID'		=> $row['group_id'],
					'GROUP_NAME'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
					'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $user->lang['GROUP_IS_SPECIAL'],
					'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
					'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],
					'GROUP_COLOUR'	=> $row['group_colour'],

					'U_VIEW_GROUP'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']),

					'S_GROUP_DEFAULT'	=> ($row['group_id'] == $user->data['group_id']) ? true : false,
					'S_ROW_COUNT'		=> ${$block . '_count'}++)
				);

				$group_id_ary[] = $row['group_id'];
			}
		}
		$db->sql_freeresult($result);

		// Hide hidden groups unless user is an admin with group privileges
		$sql_and = ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? '<> ' . GROUP_SPECIAL : 'NOT IN (' . GROUP_SPECIAL . ', ' . GROUP_HIDDEN . ')';
	}
	else
	// Always hide hidden, special, and closed groups
	{
		$sql_and = 'NOT IN (' . GROUP_SPECIAL . ', ' . GROUP_HIDDEN . ', ' . GROUP_CLOSED . ')';
	}

	$sql = 'SELECT group_id, group_name, group_colour, group_desc, group_desc_uid, group_desc_bitfield, group_desc_options, group_type, group_founder_manage
		FROM ' . GROUPS_TABLE . '
		WHERE ' . ((sizeof($group_id_ary)) ? $db->sql_in_set('group_id', $group_id_ary, true) . ' AND ' : '') . "
			group_type $sql_and
		ORDER BY group_type DESC, group_name";
	$result = $db->sql_query($sql);

	$nonmember_count = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		switch ($row['group_type'])
		{
			case GROUP_OPEN:
				$group_status = 'OPEN';
			break;

			case GROUP_CLOSED:
				$group_status = 'CLOSED';
			break;

			case GROUP_HIDDEN:
				$group_status = 'HIDDEN';
			break;

			case GROUP_SPECIAL:
				$group_status = 'SPECIAL';
			break;

			case GROUP_FREE:
				$group_status = 'FREE';
			break;
		}

		$template->assign_block_vars('groups', array(
			'GROUP_ID'		=> $row['group_id'],
			'GROUP_NAME'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
			'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? generate_text_for_display($row['group_desc'], $row['group_desc_uid'], $row['group_desc_bitfield'], $row['group_desc_options']) : $user->lang['GROUP_IS_SPECIAL'],
			'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
			'GROUP_CLOSED'	=> ($row['group_type'] <> GROUP_CLOSED || $auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? false : true,
			'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],
			'S_CAN_JOIN'	=> ($row['group_type'] == GROUP_OPEN || $row['group_type'] == GROUP_FREE) ? true : false,
			'GROUP_COLOUR'	=> $row['group_colour'],

			'U_VIEW_GROUP'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']),

			'S_ROW_COUNT'	=> $nonmember_count++)
		);
	}
	$db->sql_freeresult($result);
}

/**
* Return the PM link for the forum header, including all linked accounts
*
* @param bool $include_user include current user?
*/
function linked_pms ($include_user = true)
{
	// Disable this for now
	return false;

	global $phpbb_root_url, $phpbb_root_path, $phpEx, $user, $db;
	
	//[user_type] => 2
	if ($user->data['username'] == 'Anonymous')
	{
		return false;
	}
	
	$pms = array();
	
	$u_acount_link = $phpbb_root_path ."ucp.$phpEx?i=account_link";
	$linked_accounts = get_linked_accounts();
	
	
	foreach ($linked_accounts as $id => $username)
	{
		if ($id != $user->data['user_id'] || $include_user)
		//if ($include_user)
		{
			$l_privmsgs_text = $l_privmsgs_text_unread = '';
			$s_privmsg_new = false;
			$l_privmsgs_text_unread = '';
			
			// Get new PM count
			$sql = 'SELECT user_id, pm_new
						FROM ' . 'phpbb_privmsgs_to' . '
						WHERE user_id = ' .$id . ' AND pm_new = 1';
			
			$result = $db->sql_query($sql);
			$rows = $db->sql_fetchrowset($result);
			$new_pms = count($rows);
			
			if ($new_pms != 0)
			{
				$l_message_new = ($new_pms == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
				$l_privmsgs_text = sprintf($l_message_new, $new_pms);
			}
			
			// Get unread PM count
			$sql = 'SELECT user_id, pm_unread
						FROM ' . 'phpbb_privmsgs_to' . '
						WHERE user_id = ' .$id . ' AND pm_unread = 1';
			
			$result = $db->sql_query($sql);
			$rows = $db->sql_fetchrowset($result);
			$unread_pms = count($rows);
			
			if ($unread_pms != 0)
			{
				$l_message_unread = ($unread_pms == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
				$l_privmsgs_text_unread = sprintf($l_message_unread, $unread_pms);
			}

			$u_account_link_pm = $u_account_link . '&switch_acct=' . $id . '&redirect=' . $phpbb_root_url . '/ucp.php?i=pm&folder=inbox';
			
			
			if ($new_pms > 0 && $new_pms < $unread_pms )
			// Username: new, unread
			{
				$l_linked_pms[$id] = '<a href='
					. append_sid( "{$phpbb_root_path}ucp.$phpEx", "i=account_link&switch_acct=$id&redirect={$phpbb_root_path}ucp.php?i=pm&folder=inbox")
					. ">$username: $l_privmsgs_text, $l_privmsgs_text_unread</a>";
			}
			elseif ($new_pms > 0)
			// Username: new
			{
				$l_linked_pms[$id] = $username . ': ' . $l_privmsgs_text;
				$l_linked_pms[$id] = '<a href='
					. append_sid( "{$phpbb_root_path}ucp.$phpEx", "i=account_link&switch_acct=$id&redirect={$phpbb_root_path}ucp.php?i=pm&folder=inbox")
					. ">$username: $l_privmsgs_text</a>";
			}
			elseif ($unread_pms > 0)
			// Username: unread
			{
				$l_linked_pms[$id] = '<a href='
					. append_sid( "{$phpbb_root_path}ucp.$phpEx", "i=account_link&switch_acct=$id&redirect={$phpbb_root_path}ucp.php?i=pm&folder=inbox")
					. ">$username: $l_privmsgs_text_unread</a>";
			}
			
			//$l_linked_pms[$id] = '<a href='
			//	. append_sid( $phpbb_root_path ."ucp.$phpEx?i=account_link&switch_acct=$id&redirect=/forum/ucp.php?i=pm&folder=inbox")
			//	. ">$l_linked_pms[$id]</a>";
		}
	}
	
	if (empty($l_linked_pms))
	{
		return false;
	}
	
	return ('(' . implode(', ', $l_linked_pms) . ')');
}

?>