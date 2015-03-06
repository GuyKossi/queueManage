<?php

/**
 * Description of AppPushSender
 *
 * @author sergio
 */
class AppPushSender {

    public $token = null;

    public function __construct( $token ) {
        $this->token = $token;
    }

    public function sendYourTurn( $ticket ) {
        gfDebug( 'Send push for your turn ' .
            $ticket->getTextString() .
            ' to ' . $this->token
        );
        return $this->sendGenericPush( 'text' );
    }

    public function sendNotice( $peopleBefore, $eta ) {
        gfDebug( 'Send push (' . $peopleBefore . ', '
            . $eta . ' min) to ' . $this->token
        );
        return $this->sendGenericPush( 'text' );
    }

    public function sendTrashNotice() {
        gfDebug( 'Send push for ticket trash to ' . $this->token );
        return $this->sendGenericPush( 'text' );
    }

    private function sendGenericPush( $text ) {
        // TODO: to implement
        return true;
    }

}
