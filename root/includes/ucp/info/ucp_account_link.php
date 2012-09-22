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
* @package module_install
*/
class ucp_account_link_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_account_link',
			'title'		=> 'Account Link Mod',
			'version'	=> '0.7.9',
			'modes'		=> array(
				'switch'	=> array('title' => 'SWITCH_LINKED_ACCOUNTS', 'auth' => '', 'cat' => array('ACCOUNT_LINK')),
				'manage'	=> array('title' => 'MANAGE_LINKED_ACCOUNTS', 'auth' => '', 'cat' => array('ACCOUNT_LINK')),
				'create'	=> array('title' => 'CREATE_LINKED_ACCOUNT', 'auth' => 'acl_u_alink_create', 'cat' => array('ACCOUNT_LINK')),
				//'create_join'	=> array('title' => 'Create a Linked Account and join a Group', 'auth' => '', 'cat' => array('Account Links'))
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>