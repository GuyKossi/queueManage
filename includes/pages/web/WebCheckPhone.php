<?php

/**
 * Description of WebCheckPhone
 *
 * @author sergio
 */
class WebCheckPhone extends Page {

    private $redirect = null;
    private $phone_code = null;
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
        
        $phone_code = gfPostVar( 'phone_code', '' );
        $phone_code = strtoupper( $phone_code );
        if ( !$phone_code ) {
            $this->message = 'Il campo è obbligatorio.';
            return true;
        }

        if ( $phone_code != $this->phone_code ) {
            $this->message = 'Il codice inserito non è corretto.';
            return true;
        }

        // check td existence and activation
        $td_code = gfSessionVar( 'td_code', '' );
        $td = TopicalDomain::fromDatabaseByCode( $td_code );
        if ( !$td || !$td->getActive() ) {
            // This is very improbable in this
            // case start again the wizard
            $_SESSION['step'] = 0;
            return true;
        }

        $ticket = Ticket::nextNewTicket( $td_code, 'web', gfSessionVar( 'phone' ) );
        $ticket->save();

        $sender = new SmsSender( gfSessionVar( 'phone' ) );
        if ( !$sender->sendNewTicket( $ticket ) ) {
            // False means error while sending
            $this->message = "Errore nella prenotazione. Assicurarsi di aver inserito il numero corretto.";
            return true;
        }

        $_SESSION['step'] = 3;
        $_SESSION['ticket'] = $ticket;

        $redirect = new RedirectOutput( "$gvPath/web/complete" );
        return $redirect;

    }

    public function afterPermissionCheck() {
        $this->goBack = gfGetVar( 'goBack', false );
        if ( $this->goBack ) {
            $step = gfSessionVar( 'step', 0 );
            if ( $step == 2 ) {
                $_SESSION['step'] = 1;
            } else {
                $_SESSION['step'] = 0;
            }
        }
        $this->redirect = Session::redirectLastStep( 2 );
        $this->phone_code = gfSessionVar( 'phone_code', '' );
    }

    public function getOutput() {
        global $gvPath;

        if ( $this->redirect ) {
            return $this->redirect;
        }

        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'Verifica numero' );
        $output->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        $form = <<<EOS
$this->message
<form method="post">
    <input type="text" name="phone_code" size="20" placeholder="Codice di verifica" required />
    <br />
    <input type="submit" value="Invia">
</form>
<p><a href="?goBack=1">Torna indietro</a></p>
EOS;
        return $form;
    }

    public function getHeader() {
        return '<h1>Verifica numero</h1>';
    }

}
