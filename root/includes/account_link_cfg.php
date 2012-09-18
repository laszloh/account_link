<?php
/**
* @author Michael Flenniken, Jr drakkim@conclavewiz.com
*
* @package phpBB
* @version SVN: $Id: account_link_cfg.php 77 2012-09-02 10:48:53Z drakkim $
* @copyright (c) 2009 Michael Flenniken, Jr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* Temporary config file until I get DB config working
*
*
*/

/**
* @ignore
*/
defined( 'IN_PHPBB' ) or die('Hacking Attempt!');


$account_link_config = array(
	'group_enabled'		=> true, // or false;
	'group_required'	=> false, // or false;
	'default_group_ids'	=> array( ),
	'blacklist'			=> array('test_user', 'test_2' ),
);

?>