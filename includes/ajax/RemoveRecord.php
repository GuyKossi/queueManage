<?php

class RemoveRecord extends Page {
    
    public function canUse( $userLevel ) {
        return $userLevel == Page::SYSADMIN_USER;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $result = $this->performRemove();
        $data = array( 'result' => $result );
        $ret = new JsonOutput();
        $ret->setContent( $data );
        return $ret;
    }
    
    private function performRemove() {
        if ( isset( $_GET['op_id'] ) ){
            return $this->removeOperator();
        }
        if ( isset( $_GET['td_id'] ) ) {
            return $this->removeTopicalDomain();
        }
        if ( isset( $_GET['desk_id'] ) ) {
            return $this->removeDesk();
        }
        if ( isset( $_GET['dev_id'] ) ) {
            return $this->removeDevice();
        }
        return 'false';
    }
    
    private function removeOperator() {
        $op_id = $_GET['op_id'];
        $operator = Operator::fromDatabaseById( $op_id );
        if ( $operator ){
            if ( $operator->isOnline() ) {
                return 'operatorOnline';
            }
            if ( $operator->delete() ) {
                return 'true';
            }
        }
        return 'false';
    }
    
    private function removeTopicalDomain() {
        $td_id = $_GET['td_id'];
        $td = TopicalDomain::fromDatabaseById( $td_id );
        if ( $td ){
            if ( $td->getActive() ) {
                return 'notDeactivated';
            }
            if ( $td->delete() ) {
                return 'true';
            }
        }
        return 'false';
    }
    
    private function removeDesk() {
        $desk_id = $_GET['desk_id'];
        $desk = Desk::fromDatabaseById( $desk_id );
        if ( $desk ) {
            if ( $desk->isOpen() ) {
                return 'deskOpen';
            }
            if ( $desk->delete() ) {
                return 'true';
            }
        }
        return 'false';
    }
    
    public function removeDevice() {
        $dev_id = $_GET['dev_id'];
        $device = Device::fromDatabaseById( $dev_id );
        if ( $device ) {
            if ( $device->delete() ) {
                return 'true';
            }
        }
        return 'false';
    }

}
