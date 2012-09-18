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
				'manage'	=> array('title' => 'Manage Linked Accounts', 'auth' => '', 'cat' => array('Account Links')),
				'create'	=> array('title' => 'Create a Linked Account', 'auth' => '', 'cat' => array('Account Links')),
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