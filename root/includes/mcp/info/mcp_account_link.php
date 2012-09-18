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
* @package module_install
*/
class mcp_account_link_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_account_link',
			'title'		=> 'Account Link Mod',
			'version'	=> '0.7.9',
			'modes'		=> array(
				'manage'	=> array('title' => 'Manage Linked Accounts', 'auth' => '', 'cat' => array('Linked Accounts')),
				'moderate'	=> array('title' => 'Moderate Linked Accounts', 'auth' => 'acl_a_user', 'cat' => array('Linked Accounts')),
//				'unlink'		=> array('title' => 'Unink Accounts', 'auth' => '', 'cat' => array('Linked Accounts')),
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