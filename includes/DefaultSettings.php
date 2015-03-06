<?php

if ( !defined( 'ENTRY_POINT' ) ) {
    die('This is not an entry point. DefaultSettings' );
}

// This array contains editable settings by sysAdmin
$gvEditableConfs = array(
	// First and second entries need to be AdminCode and AdminPassword, always!
	new EditableConf( 'gvSysAdminCode', '0000', 'Codice accesso sysAdmin' ),
	new EditableConf( 'gvSysAdminPassword', 'admin0', 'Password accesso sysAdmin' ),
	new EditableConf( 'gvMinPasswordLength', 6, 'Lunghezza minima password' ),
	new EditableConf( 'gvSessionTimeout', 900, 'Durata sessione (sec)' ),
	new EditableConf( 'gvTrashThreshold', 30, 'Soglia ticket trash (sec)' ),
	new EditableConf( 'gvPhoneCodeLength', 4, 'Lunghezza codice di verifica web (max 40)' ),
	new EditableConf( 'gvQueueLengthWebLimit', 10, 'Limite lunghezza coda per prenotazione web' ),
	new EditableConf( 'gvQueueEtaWebLimit', 900, 'Limite attesa coda per prenotazione web (sec)' ),
	new EditableConf( 'gvQueueLengthAppLimit', 5, 'Limite lunghezza coda per prenotazione app' ),
	new EditableConf( 'gvQueueEtaAppLimit', 600, 'Limite attesa coda per prenotazione app (sec)' ),
	new EditableConf( 'gvQrCodeMsg', 'Testo QrCode', 'Testo al lato del QrCode', 'textarea' ),
	new EditableConf( 'gvSpotTitle', 'Titolo spot', 'Titolo dello spot del ticket' ),
	new EditableConf( 'gvSpotBody', 'Testo dello spot', 'Testo dello spot del ticket', 'textarea' ),
	new EditableConf( 'gvCallOtherTdWhenEmpty', true, 'Operatori in busy-mode' ),
	new EditableConf( 'gvAllowPause', true, 'Abilita pulsante pausa per gli operatori' ),
);


// Default settings overridden by LocalSettings
$gvLangCode = 'it';
$gvTimeZone = 'Europe/Rome';
$gvDirectory = dirname( __DIR__ );
$gvPath = '/fastqueue';
$gvServerName = 'localhost';
$gvProtocol = 'http://';
$gvPort = ''; // e.g. 8080, empty = 80 (http) or 143 (https)

$gvDbConfig = array();
$gvDbConfig['host'] = "10.10.1.200";
$gvDbConfig['database'] = "fastqueue";
$gvDbConfig['username'] = "fastqueue";
$gvDbConfig['password'] = "root";

$gvJqueryUrl = 'http://code.jquery.com/jquery-2.1.1.min.js';

// Debug settings
$gvDebug['active'] = true;
$gvDebug['destinationHost'] = '127.0.0.1';
$gvDebug['destinationPort'] = '2015';
$gvDebug['disableSms'] = true;

// Office infos
$gvOfficeCode = 'FSQIT0000001';
$gvOfficeName = 'Ufficio Postale - Isernia centro';
$gvOfficeAddress = 'Via XXIV Maggio, 234';
$gvOfficeSecret = 'ImSecret';

// Set default values for editable settings
foreach( $gvEditableConfs as $conf ) {
	$conf->exportDefault();
}
