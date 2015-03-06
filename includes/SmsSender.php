<?php

/**
 * Description of SmsSender
 *
 * @author sergio
 */
class SmsSender {

    const URL = "https://rest.nexmo.com/sms/json?api_key=e642993c&api_secret=73061bb2&from=FastQueue";

    public $phone = null;
    public $url = '';

    public function __construct( $phone ) {
        $this->phone = $phone;
        $this->url = self::URL . "&to=$phone";
    }
    
    public function sendNewTicket( $ticket ) {
        gfDebug( 'Sent sms for new ticket ' .
            $ticket->getTextString() .
            ' to ' . $this->phone
        );
        $text = <<<EOS
Gentile utente, grazie per la prenotazione.
Il tuo ticket è {$ticket->getTextString()}.
Questo sms è valido come ticket, non cancellarlo e ricordati di presentarlo all'operatore.
EOS;
        return $this->sendGenericSms( $text );
    }
    
    public function sendNotice( $peopleBefore, $eta ) {
        gfDebug(
            'Send notice (' .
            $peopleBefore . ', ' . $eta .
            ' min) to ' .
            $this->phone
        );
        $text = <<<EOS
Gentile utente, mancano $peopleBefore clienti prima del tuo turno.
Il tempo d'attesa è di $eta minuti.
Non riceverai altre notifiche.
Ti consigliamo di scaricare l'app.
EOS;
        return $this->sendGenericSms( $text );
    }

    public function sendVerificationCode( $code ) {
        gfDebug( 'Verification code ' .
            $code .
            ' sent to ' . $this->phone
        );
        $text = "Gentile utente, il codice di verifica è: " . $code;
        return $this->sendGenericSms( $text );
    }

    private function sendGenericSms( $text ) {
        global $gvDebug;

        if ( $gvDebug['active'] && $gvDebug['disableSms'] ) {
            return true;
        }

        // Get cURL resource
        $curl = curl_init();
        // Set some options
        curl_setopt_array( $curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url . "&text=" . urlencode( $text ),
        ) );
        // Send the request and save response
        $resp = curl_exec( $curl );
        gfDebug( "Ricevuta risposta\n" . $resp );
        // Close request to clear up some resources
        curl_close( $curl );
        if ( strpos( $resp , "error") !== false ) {
            return false;
        }
        return true;
    }

}
