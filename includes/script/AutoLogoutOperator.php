<?php
include '../Setup.php';

$desks = Desk::fromDatabaseCompleteList();
foreach ( $desks as $desk) {
    if ( time() - $desk->getLastActivityTime() >= $gvSessionTimeout ) {
        $desk->setLastActivityTime( null );
        $desk->setOpCode( null );
        $desk->save();
    }
}

Database::getConnection()->commit();