<?php
include '../Setup.php';

foreach ( Database::$tables as $table ) {
	$stmt = Database::prepareStatement( "TRUNCATE $table" );
	$stmt->execute();
}
?>
Tabelle svuotate correttamente