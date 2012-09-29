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
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACCOUNT_LINK'	=> 'Account Verknüpfungen',
	'ACCOUNT_LINK_EXPLAIN'	=> 'Verbindet zwei Benutzerkonten so, dass du zwischen ihnen wechseln kannst ohne dich ausloggen zu müssen.',
	
	'MANAGE_LINKED_ACCOUNTS'	=> 'Benutzerkonten verwalten',
	'MANAGE_LINKED_ACCOUNTS_EXPLAIN'	=> 'Hier können alle verknüpften Benutzerkonten verwaltet werden',
	'LINK_ACCOUNTS'				=> 'Neues Benutzerkonten verbinden',
	'VERIFY_MASTER_ACCOUNT'		=> 'Hauptkonto verifizieren',
	'UNLINK_ACCOUNTS'			=> 'Benutzerkonten trennen',
	
	'SWITCH_LINKED_ACCOUNTS'	=> 'Benutzer wechseln',
	
	// ucp config keys
	'CONFIG_LINKED_ACCOUNTS'		=> 'Account Link Einstellungen',
	'CONFIG_LINKED_POSITION'		=> 'Verknüpfte Accounts anzeigen',
	'CONFIG_POSITION_UAVA'			=> 'Unter dem Avatar',
	'CONFIG_POSITION_USIG'			=> 'Unter der Signatur',
	'CONFIG_POSITION_UMAN'			=> 'Manuell in der Signatur eingetragen',
	'CONFIG_POSITION_HIDE'			=> 'Spezielle Accounts verbergen',
	'CONFIG_POSITION_HIDE_EXPLAIN'	=> 'Verbirgt Accounts mit Administrator oder Moderatorrechten bei der Auflistung',
	'CONFIG_POSITION_MULTI_PN'		=> 'Multiaccount PN-Benachrichtigung',
	'CONFIG_ALINK_UPDATED'			=> 'Account Link Einstellungen übernommen.',
	
	'CREATE_LINKED_ACCOUNT'			=> 'Benutzerkonto erstellen',
	'CREATE_LINKED_ACCOUNT_EXPLAIN'	=> 'Erstellt einen neuen mit diesem Konto verknüpften Benutzer.',

	// Buttons and modes
	'SWITCH_USER'			=> 'Benutzer wechseln',
	'LINK_USERS'			=> 'Benutzer verknüpfen',
	'UNLINK_USERS'			=> 'Benutzer trennen',
	'NO_LINKED_ACCOUNTS'	=> 'Keine verknüpften Accounts gefunden',
	
	// ACP Fields
	'ACP_USER_ALINK'		=> 'Verknüpfte Konten',
	'LINKED_USERS'			=> 'Verlinkte Benutzerkonten',
	'MASTER_ACCOUNT'		=> 'Hauptkonto',
	'USER_UNLINK'			=> 'Benutzer entfernen',
	'USER_LINK'				=> 'Benutzerkonto hinzufügen',

	// Field labels
	'USERNAME'	=> 'Benutzername',
	'PASSWORD'	=> 'Passwort',

	'LINKED_ACCOUNTS'	=> 'Verknüpfte Konten',
	'HIDDEN_ACCOUNT'	=> '--- ausgeblendet ---',

	// Messages
	'LINK_SUCCESS'					=> 'Verknüpfung erfolgreich',
	'CONFIRM_ACCOUNT_UNLINK'		=> 'Soll Benutzer "%s"  aus der Verlinkung mit "%s" entfernt werden?',
	'LINK_BROKEN'					=> 'Verknüpfung gelöscht',
	'ACCOUNT_UNLINK_FAILED'			=> 'Verlinkung wurde nicht gelöscht',
	'ACCOUNT_SWITCH_REDIRECT'		=> 'Du hast dich erfolgreich als %s eingeloggt.',
	'LINKED_ACCOUNT_CREATED'		=> 'Das Konto \'%1$s\' wurde erfolgreich erstellt und mit \'%2$s\' verknüpft.',
	'LINKED_GROUP_JOINED'			=> 'Du bist der Gruppe(n) %s erfolgreich beigetreten.',
	'LINKED_GROUP_JOINED_PENDING'	=> 'Du hast erfolgreich um Mitgliedschaft bei folgenden Gruppe(n) angesucht: %s. Der Gruppenführer muss noch deine Mitgliedschaft bestätigen.',
	
	// Log entries
	'LOG_LINK_SUCCESS'			=> 'Benutzer %s und %s wurden erfolgreich verlinkt.',
	'LOG_LINK_FAILED'			=> 'Verlinkung von %s und %s fehlgeschlagen.',
	'LOG_LINK_BROKEN'			=> 'Verlinkung zwischen %s und %s wurde gelöst',
	'LOG_LINK_BROCKEN_FAILED'	=> 'Verlinkung zwischen %s und %s konnte nicht gelöst werden',
	'LOG_ALINK_SWITCH_NO_UID'	=> 'Beim wechseln von %s wurde keine neue UID mitgeliefert',

	// Errors
	'ALINK_GENERAL_ERROR'	=> 'Es ist ein allgemeiner Fehler aufgetreten. Bitte versuche es später wieder. <br />Sollte der Fehler weiterhin bestehen bleiben, wende dich an <a href="/the-team.html">das Team</a>',
	'NO_ACCOUNTS_LINKED'	=> 'Es sind keine Konten mit diesem Benutzer verknüpft',
	'BAD_MASTER_PASSWORD'	=> 'Falsches Passwort für das Hauptkonto',
	'BAD_LINKED_PASSWORD'	=> 'Benutzername oder Passwort für die neue Verknüpfung falsch.',
	'ACCOUNT_NOT_LINKED'	=> 'Die beiden Benutzer %s und %s sind nicht verknüpft.',
	'USERNAME_PASS_REQUIRED'=> 'Ein neue Benutzername und Passwort sind erforderlich',
	'CANNOT_LINK_MASTER'	=> 'Ein schon verknüpfte Benutzerkonto kann nicht noch einmal verknüpft werden!',
	'NOT_NORMAL_USER'		=> 'Es kann kein inaktiver Benutzer oder Gründer über das MCP oder ACP verknüpft werden',
	'LINKED_GROUP_REQUIRED'	=> 'Du musst eine Benutzergruppe auswählen. Verwende bitte die "Zurück"-Taste deines Browsers.',
	
	'ACCOUNT_SWITCH_ERROR'	=> 'Beim wechseln zwischen den Konten ist ein Fehler aufgetreten. Du könntest ausgeloggt werden.<br />Sollte der Fehler weiterhin bestehen bleiben, wende dich an <a href="/the-team.html">das Team</a>',
	'NOT_MASTER'			=> 'Dieser Benutzerkonto ist mit einem anderen Verknüpft. Wechsle bitte zu dem Hauptaccount <b>%s</b> um es zu verwalten.',
	'NEW_INFO_REQUIRED'		=> 'Ein neue Benutzername und Passwort sind erforderlich',
));
			
?>