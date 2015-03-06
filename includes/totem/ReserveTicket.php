<?php

/**
 * Description of ReserveTicket
 *
 * @author sergio
 */
class ReserveTicket extends Page {
    
    public function canUse($userLevel) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $output = new JsonOutput();

        $td_code = gfGetVar( 'td_code', null );
        if ( !$td_code ) {
            $output->setContent( array( 'result' => 'error' ) );
            return $output;
        }

        // check td existence and activation
        $td = TopicalDomain::fromDatabaseByCode( $td_code );
        if ( !$td ) {
            $output->setContent( array( 'result' => 'error' ) );
            return $output;
        }
        if ( !$td->getActive() ) {
            $output->setContent( array( 'result' => 'error' ) );
            return $output;
        }

        $ticket = Ticket::nextNewTicket( $td_code, 'totem' );
        $ticket->save();

        list( $queueLength, $eta ) = $ticket->getPeopleAndEta();
        $content = array(
            'code' => $ticket->getCode(),
            'number' => $ticket->getNumber(),
            'eta' => $eta,
            'queueLength' => $queueLength,
        );

        $jsonOutput = new JsonOutput();
        $jsonOutput->setContent( $content );
        
        return $jsonOutput;
    }

}
