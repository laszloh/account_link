<?php
/** 
* Account Link [English]
*
* @author Michael Flenniken, Jr. <drakkim@conclavewiz.com>
*
* @package language
* @version $Id: account_link.php 77 2012-09-02 10:48:53Z drakkim $
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACCOUNT_LINK'	=> 'Account Links',
	'ACCOUNT_LINK_EXPLAIN'	=> 'Link accounts so you can easily switch between them without logging out.',
	
	'CREATE_LINKED_ACCOUNT'			=> 'Create Linked Account',
	'CREATE_LINKED_ACCOUNT_EXPLAIN'	=> 'Create a new account that linked to this one. You can easily switch between these accounts without logging out.',

	// Buttons and modes
	'SWITCH_USER'	=> 'Switch User',
	'LINK_USERS'	=> 'Link Users',
	'UNLINK_USERS'	=> 'Unlink Users',

	// Field labels
	'MASTER_NAME'	=> 'Master Username',
	'MASTER_PASS'	=> 'Master Password',
	'LINKED_NAME'	=> 'Linked Username',
	'LINKED_PASS'	=> 'Linked User Password',

	'LINKED_ACCOUNTS'	=> 'Linked Accounts',

	// Messages
	'LINK_SUCCESS'					=> 'Link successful',
	'LINK_BROKEN'					=> 'Link removed',
	'ACCOUNT_SWITCH_REDIRECT'		=> 'You have successfully logged in as %s.',
	'LINKED_ACCOUNT_CREATED'		=> 'The account \'%1$s\' has been created and linked to \'%2$s\'.',
	'LINKED_GROUP_JOINED'			=> 'Successfully joined the following group(s) %s.',
	'LINKED_GROUP_JOINED_PENDING'	=> 'Successfully requested membership of the following group(s): %s. Please wait for the group leader(s) to approve your membership.',

	// Errors
	'NO_ACCOUNTS_LINKED'	=> 'No accounts are linked to this one',
	'BAD_LINKED_USERNAME'	=> 'Username not found: %s',
	'BAD_MASTER_PASSWORD'	=> 'Invalid master account password',
	'BAD_LINKED_PASSWORD'	=> 'Link username or password invalid',
	'ACCOUNT_NOT_LINKED'	=> 'User accounts %s and %s are not linked.',
	'USERNAME_PASS_REQUIRED'=> 'New username and password are required',
	'CANNOT_LINK_MASTER'	=> 'Cannot link an account that is already linked!.',
	'NOT_NORMAL_USER'		=> 'Cannot link inactive users or founders from MCP or ACP',
	'LINKED_GROUP_REQUIRED'	=> 'You must choose a group to join. Please use your browser\'s back button.',
	
	'ACCOUNT_SWITCH_ERROR'	=> 'An error occured switching accounts. You may be logged out.',
	'NOT_MASTER'			=> 'This account is linked to another. Please switch to the master account (%s) to manage account links.',
	'NEW_INFO_REQUIRED'		=> 'New username and password are required',
));
			
?>