<?php
include '../includes/Setup.php';
include './displayFunctions.php';

// Do not lock tables
Database::lockTables( false );

$deskNumber = gfGetVar( 'deskNumber' );

// Check whether this device needs to be redirected somewhere
$dev = Device::fromDatabaseByIpAddress( $_SERVER['REMOTE_ADDR'] );
if ( $content = getContentForRedirect( $dev, (int) $deskNumber ) ) {
    $output = new JsonOutput();
    $output->setContent( $content );
    $output->output();
    exit();
}

// Response is json
$output = new JsonOutput();

$pageTicketCode = gfGetVar( 'ticketCode', '' );
$pageTicketNumber = gfGetVar( 'ticketNumber', '' );

$startTime = time();
while ( time() - $startTime < 120) {
	sleep( 1 ); // Check every second

    // Update table for session timed out
    $desk = Desk::fromDatabaseByNumber( $deskNumber );
    $desk->isOpen();

	$currentTicket = Ticket::fromDatabaseByDesk( $deskNumber );
	if ( !$currentTicket ) {
        if ( $pageTicketCode == '' && $pageTicketNumber == '' ) {
            // Still no ticket
            continue;
        }
        // Desk is closing send empty stuff
        $output->setContent(
            array(
                'status' => 'changed',
                'code' => '',
                'number' => '',
            )
        );
        $output->output();
        die();
	}

	if (
		$currentTicket->getCode() != $pageTicketCode
		|| $currentTicket->getNumber() != $pageTicketNumber
	) {
		// New ticket called
		$output->setContent(
			array(
                'status' => 'changed',
				'code' => $currentTicket->getCode(),
				'number' => $currentTicket->getNumber(),
			)
		);
        $output->output();
        die();
	}
} // End while
// Polling wait expired
$output->setContent( array( 'status' => 'unchanged' ) );
$output->output();
die();