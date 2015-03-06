<?php

/**
 * Description of WebComplete
 *
 * @author sergio
 */
class WebComplete extends Page {

    private $ticket = null;
    private $redirect = null;
	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function afterPermissionCheck() {
        $this->redirect = Session::redirectLastStep( 3 );
        $this->ticket = gfSessionVar( 'ticket', null );
    }

    public function getOutput() {
        global $gvPath;

        if ( $this->redirect ) {
            return $this->redirect;
        }

        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'Prenotazione completata' );
        $output->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        global $gvPath;
        $content = <<<EOS
<p>
È stato prenotato il ticket {$this->ticket->getTextString()}<br />
Verrai avvisato quando mancano {$this->ticket->getNoticeCounter()} clienti prima di te.
</p>
<p>
<a href="$gvPath/web/tdSelection?reset=1">Torna all'elenco dei servizi</a>
</p>
EOS;
        return $content;
    }

    public function getHeader() {
        return '<h1>Prenotazione completata</h1>';
    }

}
