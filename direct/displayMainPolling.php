<?php
include '../includes/Setup.php';
include './displayFunctions.php';

// Do not lock tables: we are only reading
Database::lockTables( false );

// Leave file scope
function main() {

    // Check whether this device needs to be redirected somewhere
    $dev = Device::fromDatabaseByIpAddress( $_SERVER['REMOTE_ADDR'] );
    if ( $content = getContentForRedirect( $dev, 0 ) ) {
        $output = new JsonOutput();
        $output->setContent( $content );
        $output->output();
        exit();
    }

    $td_code = gfGetVar( 'td_code', '' );
    $lastTicket = gfGetVar( 'lastTicket', 'A000' );
    $content = null;
    $startTime = time();
    while ( time() - $startTime < 120) {
        sleep( 1 ); // Check every second

        $currentState = DisplayMain::fromDatabaseCompleteList( $td_code );
        if ( count( $currentState ) == 0 ) {
            continue;
        }
        if ( $lastTicket != $currentState[0]->getTicket() ) {
            // Something changed, compute new tickets
            $newTickets = array( $currentState[0] );
            $i = 1;
            while (
                $i < count( $currentState )
                && $i < 8
                && $lastTicket != $currentState[$i]->getTicket()
            ) {
                $newTickets[] = $currentState[$i];
                $i++;
            }
            $content = array(
                    'status' => 'changed',
                    'count' => count( $newTickets ),
                    'entries' => $newTickets
            );
            break;
        }
        unset( $currentState );
    } // End while

    if ( !$content ) {
        // Polling wait expired
        $content = array( 'status' => 'unchanged' );
    }

    $output = new JsonOutput();
    $output->setContent( $content );
    $output->output();
}

main();
