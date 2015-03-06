<?php

/**
 * Description of ApiPage
 *
 * @author sergio
 */
class ApiPage extends Page {

    public $content = null;

    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        if ( !isset( $_POST['deviceToken'] ) ) {
            $this->setTokenError();
            return true;
        }
        $action = gfPostVar( 'action', '' );
        $class = AppAction::getClass( $action );
        try {
            $apiAction = new $class( $_POST['deviceToken'] );
            $this->content = $apiAction->execute();
        } catch ( BadOwnershipException $e ) {
            $this->setOwnershipError();
        } catch ( BannedDeviceException $e ) {
            $this->setBannedError();
        } catch ( InvalidOfficeCodeException $e ) {
            $this->setOfficeCodeError();
        } catch ( InvalidParamException $e ) {
            $this->setInvalidParamError();
        } catch ( InvalidTicketException $e ) {
            $this->setTicketError();
        } catch ( InvalidTokenException $e ) {
            $this->setTokenError();
        } catch ( Exception $e ) {
            $this->setInternalError( $e );
        }
        return true;
    }

    public function getOutput() {
        if ( $this->content == null ) {
            $this->setNotPostError();
        }
        $output = new JsonOutput();
        $output->setContent( $this->content );
        return $output;
    }


    public function setBannedError() {
        $this->content = array(
            "ErrorCode" => "AE001",
            "ErrorMsg" => "Device is banned",
        );
    }

    public function setInvalidParamError() {
        // Some parameters have not been provided
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Invalid parameters provided",
        );
    }

    public function setTokenError() {
        // Token is not valid
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Invalid token provided",
        );
    }

    public function setOfficeCodeError() {
        // Office code sent by app is not equal to
        // the office code of *this* office
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Invalid office code provided",
        );
    }

    public function setNotPostError() {
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Request not via POST",
        );
    }

    public function setInternalError( $e ) {
        global $gvDebug;
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Internal error while processing the request",
        );
        if ( $gvDebug['active'] ) {
            $this->content['Debug'] = print_r( $e, true );
        }
    }

    public function setTicketError() {
        $this->content = array(
            "ErrorCode" => "AE???",
            "ErrorMsg" => "Ticket does not exist",
        );
    }

    public function setOwnershipError() {
        $this->content = array(
            "ErrorCode" => "AE007",
            "ErrorMsg" => "Device does not own the ticket",
        );
    }
}
