<?php
/** 
* Account Link [German]
*
* @author Michael Flenniken, Jr. <drakkim@conclavewiz.com>
*
* @package language
* @version $Id: account_link.php 69 2011-10-08 01:32:58Z drakkim $
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
	'ACCOUNT_LINK'	=> 'Benutzerkontenverknüpfung',
	'ACCOUNT_LINK_EXPLAIN'	=> 'Verknüpft Benutzerkonten so, dass Sie leicht zwischen ihnen wechseln können, ohne sich abmelden zu müssen.',

	// Buttons and modes
	'SWITCH_USER'	=> 'Benutzer wechseln',
	'LINK_USERS'	=> 'Benutzer verknüpfen',
	'UNLINK_USERS'	=> 'Benutzerverknüpfung aufheben',

	// Field labels
	'MASTER_NAME'	=> 'Hauptbenutzername',
	'MASTER_PASS'	=> 'Hauptpasswort',
	'LINKED_NAME'	=> 'Verknüpfter Benutzername',
	'LINKED_PASS'	=> 'Passwort des verknüpften Benutzers',

	'LINKED_ACCOUNTS'	=> 'Verknüpfte Benutzerkonten',

	// Messages
	'LINK_SUCCESS'					=> 'Verknüpfung erfolgreich',
	'LINK_BROKEN'					=> 'Verknüpfung aufgehoben',
	'ACCOUNT_SWITCH_REDIRECT'		=> 'Sie haben sich erfolgreich als %s angemeldet.',
	'LINKED_ACCOUNT_CREATED'		=> 'Das Benutzerkonto \'%1$s\' wurde erstellt und mit \'%2$s\' verknüpft.',
	'LINKED_GROUP_JOINED'			=> 'Sie sind erfolgereich der/den folgenden Gruppe(n) beigetreten: %s.',
	'LINKED_GROUP_JOINED_PENDING'	=> 'Sie haben erfolgreich Mitgliedschaft für die folgende(n) Gruppe(n) beantragt: %s. Bitte warten Sie bis die Gruppenleitung Ihre Mitgliedschaft bestätigt.',

	// Errors
	'NO_ACCOUNTS_LINKED'	=> 'Es sind keine weiteren Benutzerkonten mit diesem verknüpft.',
	'BAD_LINKED_USERNAME'	=> 'Folgender Benutzername wurde nicht gefunden: %s',
	'BAD_MASTER_PASSWORD'	=> 'Ungültiges Hauptpasswort',
	'BAD_LINKED_PASSWORD'	=> 'Der verknüpfte Benutername oder das Passwort ist ungültig.',
	'ACCOUNT_NOT_LINKED'	=> 'Die Benutzerkonten %s und %s sind nicht verknüpft.',
	'USERNAME_PASS_REQUIRED'=> 'Neuer Benutzername und Passwort sind notwendig.',
	'CANNOT_LINK_MASTER'	=> 'Ein bereits verknüpfter Account kann nicht verknüpft werden!',
	'NOT_NORMAL_USER'		=> 'Inaktive Benutzer können nicht mit Gründern von MCP oder ACP verknüpft werden.',
	
	'ACCOUNT_SWITCH_ERROR'	=> 'Ein Fehler ist beim wechseln der Benutzerkonten aufgetreten. Sie könnten abgemeldet sein.',
	'NOT_MASTER'			=> 'Dieses Benutzerkonto ist mit einem anderen verknüpft. Bitte wechseln Sie zum Hauptkonto (%s), um die Benutzerkontenverknüpfung zu verwalten.',
	'NEW_INFO_REQUIRED'		=> 'Neuer Benutzername und Passwort sind notwendig.',
	
	'CREATE_LINKED_ACCOUNT' => 'Erstellen Sim ein verknüpftes Benutzerkonto.',
	
	// Log Entries
	'LOG_LINK_SUCCESS'		=> 'Hauptbenutzer \'%1$s\' wurde mit \'%2$s%\'verknüpft.',
	'LOG_LINK_FAILED'		=> 'Das Verknüpfen des Hauptbenutzers \'%1$s\' mit \'%2$s%\' ist gescheitert: %3$s%',
	'LOG_LINK_BROKEN'		=> 'Verknüpfung der Benutzer %1$s% und %2$s wurde aufgehoben.'
));
			
?>