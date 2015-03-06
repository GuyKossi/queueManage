<?php

/**
 * Description of ActivateTopicalDomain
 *
 * @author sergio
 */
class ActivateTopicalDomain extends Page {
    

    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $response = $this->performRequest();
        $json = new JsonOutput();
        $json->setContent( $response );
        return $json;
    }
    
    private function performRequest() {
        $resultFalse = array( 'result' => 'false' );
        if ( !isset( $_GET['td_id'] ) ) {
            return $resultFalse;
        }
        $td_id = $_GET['td_id'];
        $td = TopicalDomain::fromDatabaseById( $td_id );
        if ( !$td ) {
            return $resultFalse;
        }
        
        if ( $td->getActive() == 1 && !$td->canBeDeactivated() ) {
            return array( 'result' => 'ticketInQueue' );
        }
        $td->setActive( $td->getActive() ? 0 : 1 );
        if ( $td->save() ) {
            return array( 'result' => 'true' );
        }
        return $resultFalse;
    }

}
