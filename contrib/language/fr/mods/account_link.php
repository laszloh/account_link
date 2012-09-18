<?php
/** 
* Account Link [French]
*
* @author Michael Flenniken, Jr. <drakkim@conclavewiz.com>
*
* @package language
* @version $Id: account_link.php 48 2009-11-03 18:43:25Z drakkim $
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
	'ACCOUNT_LINK'	=> 'Gestion des multicomptes',
	'ACCOUNT_LINK_EXPLAIN'	=> 'Permet de lier les multicomptes pour facilement switcher entre eux sans avoir &agrave; vous d&eacute;connecter.',

	// Buttons and modes
	'SWITCH_USER'	=> 'Changer de compte utilisateur',
	'LINK_USERS'	=> 'Lier le compte',
	'UNLINK_USERS'	=> 'D&eacute;lier le compte',

	// Field labels
	'MASTER_NAME'	=> 'Pseudo du Compte Principal',
	'MASTER_PASS'	=> 'Mot de passe du Compte Principal',
	'LINKED_NAME'	=> 'Pseudo du compte li&eacute;',
	'LINKED_PASS'	=> 'Mot de passe du compte li&eacute;',

	'LINKED_ACCOUNTS'	=> 'Multicompes',

	// Messages
	'LINK_SUCCESS'	=> 'Lien cr&eacute;&eacute;',
	'LINK_BROKEN'	=> 'Lien supprim&eacute;',
	'ACCOUNT_SWITCH_REDIRECT'	=> 'Vous &ecirc;tes d&eacute;sormais connect&eacute; au compte %s.',

	// Errors
	'NO_ACCOUNTS_LINKED'	=> 'Il n\'y a pas de compte li&eacute; &agrave; celui-ci',
	'BAD_LINKED_USERNAME'	=> 'Pseudo introuvable : %s',
	'BAD_MASTER_PASSWORD'	=> 'Mot de passe principal incorrect',
	'BAD_LINKED_PASSWORD'	=> 'Mot de passe ou pseudo du compte li&eacute; incorrect',
	
	'ACCOUNT_SWITCH_ERROR'	=> 'Une erreur s\'est produite au cours du switch, vous &ecirc;tes peut-&ecirc;tre déconnect&eacute;.',
	'NOT_MASTER'			=> 'Ce compte est li&eacute; &agrave; un autre. Logguez vous sous votre compte Principal (%s) pour la gestion de vos comptes li&eacute;s.',					
));
			
?>