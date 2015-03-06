<?php

/**
 * Description of WebInsertPhone
 *
 * @author sergio
 */
class WebInsertPhone extends Page {

    private $redirect = null;
    private $td_code = null;
    private $goBack = false;
    private $message = '';
	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        global $gvPath, $gvPhoneCodeLength;

        if ( $this->redirect ) {
            return $this->redirect;
        }

        $this->message = '';
        
        $phone = gfPostVar( 'phone' );
        if ( !$phone ) {
            $this->message = 'Il campo è obbligatorio.';
            return true;
        }

        // Check only digits have been typed
        if ( !preg_match( '/^[0-9]{5,}$/', $phone ) ) {
            $this->message = 'Il valore inserito non è valido.';
            return true;
        }

        // Remove international prefix if present
        $phone = preg_replace( '/^(00|\+)?39/' , '', $phone);
        $phone = '39' . $phone;

        // Check no ticket is reserved with this phone number
        $ticket = Ticket::fromDatabaseBySourceId( $phone );
        if ( $ticket ) {
            $this->message = 'Hai già prenotato un ticket con questo numero.';
            return true;
        }

        // Check phone number is not banned
        if ( Ban::isBanned( $phone ) ) {
            $this->message = 'Questo numero di telefono è stato bloccato.';
            return true;
        }

        $hashRandom = (string) mt_rand( 0, 100000 );
        $hashRandom .= (string) mt_rand( 0, 100000 );
        $hashRandom = strtoupper( sha1( $hashRandom ) );
        $positionRandom = mt_rand( 0, 40 - $gvPhoneCodeLength );
        $phone_code = substr( $hashRandom, $positionRandom, $gvPhoneCodeLength );

        $_SESSION['phone_code'] = $phone_code;
        $_SESSION['phone'] = $phone;

        // Send SMS
        $sender = new SmsSender( $phone );
        if ( !$sender->sendVerificationCode( $phone_code ) ) {
            $this->message = 'Errore nell\'invio del messaggio. Verificare che il numero di telefono sia corretto.';
            return true;
        }

        $_SESSION['step'] = 2;

        $redirect = new RedirectOutput( "$gvPath/web/checkPhone" );
        return $redirect;

    }

    public function afterPermissionCheck() {
        $this->goBack = gfGetVar( 'goBack', false );
        if ( $this->goBack ) {
            $_SESSION['step'] = 0;
        }
        $this->redirect = Session::redirectLastStep( 1 );
        $this->td_code = gfSessionVar( 'td_code', '' );
    }

    public function getOutput() {
        global $gvPath;

        if ( $this->redirect ) {
            return $this->redirect;
        }

        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'Inserimento numero' );
        $output->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        $form = <<<EOS
<p>Stai prenotando un ticket per l'area tematica $this->td_code</p>
$this->message
<form method="post">
    <input type="text" name="phone" size="20" placeholder="Numero di cellulare" required />
    <br />
    <input type="submit" value="Invia">
</form>
<p><a href="?goBack=1">Torna indietro</a></p>
EOS;
        return $form;
    }

    public function getHeader() {
        return '<h1>Inserimento numero</h1>';
    }

}
