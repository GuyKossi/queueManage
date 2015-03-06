<?php

/**
 * Description of LogoutPage
 *
 * @author sergio
 */
class LogoutPage extends Page {
    
    public function canUse( $userLevel ) {
        // Only logged in user can logout
        return $userLevel === Page::OPERATOR_USER 
               || $userLevel === Page::SYSADMIN_USER;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvPath;

        if ( $_SESSION['userLevel'] === Page::OPERATOR_USER ) {
        	Session::logoutOperator();
        }

        session_destroy();
        unset( $_SESSION );
        session_start();
        session_regenerate_id();
        gfSetDelayedMsg('Logout effettuato correttamente.');

        $redirect = new RedirectOutput( "$gvPath/application/logoutPage" );
        return $redirect;
    }
}
