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
		global $user, $config;
	
		$user->add_lang('mods/account_link');
	}
	
	/**
	 * Fills out the template variable with the linked account informations.
	 * 
	 * @param $user_id	The user id to be queried. If 0 the current user will be taken
	 */
	function get_linked_accounts_tmplt ($user_id = 0)
	{
		global $user, $phpbb_root_path, $phpEx, $template;
		
		$master_id = $this->get_master_account($user_id);
		$linked_acc = $this->get_linked_accounts($user_id);
		
		$this->_fill_linked_blockvar($linked_acc, 'linked');
		
		$privmsg = $this->_get_linked_privmsg($linked_acc);
		$privmsg_text = $this->_generate_message_text($privmsg['new'], $privmsg['unread']);
		
		$template->assign_vars(array(
			'ALINK_PRIVMSG_TEXT'	=> $privmsg_text['new_text'],
			'ALINK_PRIVMSG_UNREAD'	=> $privmsg_text['unread_text'],
			'USER_ID'				=> $user->data['user_id'],
			
			'S_ALINK_PM_MODE'		=> ($linked_acc[$master_id]['alink_pm_mode']) ? true : false,

			'U_THIS_URL'			=> build_url(),
			'U_PM_BOX'				=> append_sid($phpbb_root_path ."ucp.{$phpEx}?i=pm&folder=inbox"),
			'U_SWITCH_PROFILE'		=> append_sid($phpbb_root_path ."alink.{$phpEx}"),
		));
	}
	
	function get_linked_account_postrow($user_id = 0)
	{
		global $template;
		
		$master_id = $this->get_master_account($user_id);
		$linked_acc = $this->get_linked_accounts($user_id);
		
		$this->_fill_linked_blockvar($linked_acc, 'postrow.linked');
	}
	
	function get_linked_account_settings($user_id = 0)
	{
		$master_id = $this->get_master_account($user_id);
		$linked_acc = $this->get_linked_accounts($user_id);
				
		$auth2 = new auth();
		$auth2->acl($linked_acc[$user_id]);

		$ret = array(
			'S_SPECIAL_USER' => ($auth2->acl_gets('a_', 'm_')) ? true : false,
		
			'S_ALINK_POSITION'	=> $linked_acc[$master_id]['alink_position'],
			'S_ALINK_HIDE'		=> ($linked_acc[$master_id]['alink_hidespeci']) ? true : false,
		);
		return $ret;
	}
	
	function _generate_message_text($new_pm = 0, $unread_pm = 0)
	{
		global $user;
	
		$ret = array('new_text' => '', 'unread_text' => '');

		if($new_pm)
		{
			$l_new_privmsg = ($new_pm == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
			$ret['new_text'] = sprintf($l_new_privmsg, $new_pm);
		}
		else
		{
			$ret['new_text'] = $user->lang['NO_NEW_PM'];
		}

		if ($unread_pm && $unread_pm != $new_pm)
		{
			$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
			$ret['unread_text'] = sprintf($l_message_unread, $unread_pm);
		}
		return $ret;
	}
	
	function _fill_linked_blockvar(&$linked_acc = array(), $block_name = 'linked')
	{
		global $template;
	
		foreach($linked_acc as $linked)
		{
			$auth2 = new auth();
			$auth2->acl($linked);
			
			$privmsg_text = $this->_generate_message_text($linked['user_new_privmsg'], $linked['user_unread_privmsg']);
			
			$template->assign_block_vars($block_name, array(
				'USER_ID'		=> $linked['user_id'],
				'USERNAME'		=> $linked['username'],
				'USER_NAME'		=> get_username_string('no_profile', $linked['user_id'], $linked['username'], $linked['user_colour']),
				'USER_PROFILE'	=> get_username_string('full', $linked['user_id'], $linked['username'], $linked['user_colour']),
				'SPECIAL_USER'	=> ($auth2->acl_gets('a_', 'm_')) ? true : false,

				'NEW_PM_TEXT'	=> $privmsg_text['new_text'],
			));
		}
	}
	
	function _get_linked_privmsg(&$linked_acc)
	{
		$retval = array('new' => 0, 'unread' => 0);
		foreach($linked_acc as $linked)
		{
			$retval['new'] = $linked['user_new_privmsg'];
			$retval['unread'] = $linked['user_unread_privmsg'];
		}
		return $retval;
	}
	
	/**
	 * Retrieve all linked account rows for $user_id
	 *
	 * @param $user_id	The user id to be queried. If 0 the current user will be taken
	 * @return an array containing the user rows of all linked accounts, including the queried account
	 */
	function get_linked_accounts ($user_id = 0)
	{
		global $db, $user;

		if ($user->data['user_id'] == ANONYMOUS && $user_id == 0)
		{
			return array();
		}

		$user_id = ($user_id == 0) ? $user->data['user_id'] : $user_id;
		$master_id = $this->get_master_account($user_id);

		$sql_array = array(
			'SELECT'	=> 'u.*',
			'FROM'		=> array(
				USERS_TABLE	=> 'u',
			),
			'WHERE'		=> '(user_id = ' . $user_id . ')
							OR (master_id = ' . $master_id . ')',
			);

		// Look up all users with user_id or master_id matching $user_id or $master_id
		if ($master_id != 0)
		{
			$sql_array['WHERE'] .= ' OR (user_id = '. $master_id .')
							   OR (master_id = '. $master_id .')';
		}
		
		$result = $db->sql_query($db->sql_build_query('SELECT', $sql_array));
		while($row=$db->sql_fetchrow($result))
		{
			$linked_accounts[$row['user_id']] = $row;
		}
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
					WHERE user_id = '. $link_user['user_id'];
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
		$sql = 'SELECT user_id, user_password, username_clean, master_id'.
			' FROM ' . USERS_TABLE .
			' WHERE (username_clean = \''. $db->sql_escape($master_username) .'\')';
		$result = $db->sql_query($sql);
		$master_user = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$sql = 'SELECT user_id, user_password, username_clean, master_id'.
			' FROM ' . USERS_TABLE .
			' WHERE (username_clean = \''. $db->sql_escape($linked_username) .'\')';
		$result = $db->sql_query($sql);
		$linked_user = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		// Validate users...
		$error = array();

		// Does $linked_user exist?
		if ($linked_user['username_clean'] != $linked_username)
		{
			$error[] = sprintf($user->lang['BAD_LINKED_USERNAME'], $linked_username);
		}
		
		// Check Master's password
		if (!phpbb_check_hash($master_pass, $master_user['user_password']))
		{
			$error[] = $user->lang['BAD_MASTER_PASSWORD'];
		}
		
		// Check Linked's password
		if (!phpbb_check_hash($linked_pass, $linked_user['user_password']))
		{
			$error[] = $user->lang['BAD_LINKED_PASSWORD'];
		}
		
		// Check if linked account is already used
		if ($linked_user['master_id'] != 0)
		{
			$error[] = $user->lang['CANNOT_LINK_MASTER'];
		}
		
		// check if linked account is a master
		$sql = 'SELECT COUNT(user_id) AS count 
			FROM ' . USERS_TABLE . ' 
			WHERE master_id = ' . $linked_user['user_id'];
		$result = $db->sql_query($sql);
		if( ((int) $db->sql_fetchfield('count')) > 0)
		{
			$error[] = $user->lang['CANNOT_LINK_MASTER'];
		}
		$db->sql_freeresult($result);
		
		// Destroy plaintext passwords... just in case.
		unset($master_pass, $linked_pass);
		
		if (!sizeof($error))
		{
			return $this->_link_accounts($master_user['user_id'], $linked_user['user_id']);
		} 
		else 
		{
			return $error;
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
		
		$linked_accts = $this->get_linked_accounts($user_id);
		foreach($linked_accts as $linked)
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
	 * Returns the number of accounts in this link. It returns 0 if the 
	 * account is not in a link.
	 *
	 * @param $user_id	The user_id to be queried. If 0 the current user will 
	 *					be taken
	 * @return	The number of accounts in this link. Zero is returned if there
	 *			is no link
	 */
	function count_linked_accounts($user_id = 0)
	{
		$count = sizeof($this->get_linked_accounts($user_id));
		return ($count == 1) ? 0 : $count;
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

?>