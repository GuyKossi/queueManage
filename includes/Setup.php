<?php
define( "ENTRY_POINT", true);

// PHP should keep sessions for at least 1 hour
ini_set( 'session.gc_maxlifetime', 3600 );

// Autoloader
include 'Autoloader.php';
spl_autoload_register( 'Autoloader::autoload' );

// Load general functions
include 'GeneralFunctions.php';

// Load settings
include 'DefaultSettings.php';

if ( file_exists( $gvDirectory . '/LocalSettings.php' ) ) {
	include $gvDirectory . '/LocalSettings.php';
} else {
	gfDebug( 'Warning: no LocalSettings found!' );
}