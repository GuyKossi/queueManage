<?php

/**
 * Description of WebTdSelection
 *
 * @author sergio
 */
class WebTdSelection extends Page {

    private $tdList = array();
    private $redirect = null;
    private $message = '';
	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        global $gvPath, $gvQueueLengthWebLimit, $gvQueueEtaWebLimit;

        if ( $this->redirect ) {
            return $this->redirect;
        }
        
        $td_code = gfPostVar( 'td_code' );
        if ( !$td_code ) {
            return true;
        }

        // Check existence of topical domain
        $td = TopicalDomain::fromDatabaseByCode( $td_code );
        if ( !$td || !$td->getActive() ) {
            $this->message = 'Non è possibile selezionare l\'area tematica indicata.';
            return true;
        }

        // Check Eta limit
        if ( $td->getEta() < $gvQueueEtaWebLimit ) {
            $message = 'È possibile selezionare solo aree tematiche con almeno %d minuti d\'attesa.';
            $this->message = sprintf( $message, (int) ( $gvQueueEtaWebLimit / 60 ) );
            return true;
        }

        // Check queue length limit
        $queueLength = Ticket::getNumberTicketInQueue( $td_code );
        if ( $queueLength < $gvQueueLengthWebLimit ) {
            $message = 'È possibile selezionare solo aree tematiche con almeno %d clienti in coda.';
            $this->message = sprintf( $message, $gvQueueLengthWebLimit );
            return true;
        }

        $_SESSION['step'] = 1;
        $_SESSION['td_code'] = $td_code;

        $redirect = new RedirectOutput( "$gvPath/web/insertPhone" );
        return $redirect;

    }

    public function afterPermissionCheck() {
        if ( gfGetVar( 'reset', false ) ) {
            Session::setupWebSession();
        }
        $this->redirect = Session::redirectLastStep( 0 );
        $this->tdList = TopicalDomain::fromDatabaseCompleteList();
    }

    public function getOutput() {
        global $gvPath;

        if ( $this->redirect ) {
            return $this->redirect;
        }

        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'Prenotazione ticket online' );
        $output->importJquery();
        $output->addJavascript( "$gvPath/assets/js/web/tdSelection.js" );
        $output->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        $content = $this->message . PHP_EOL;
        $content .= $this->getTable();
        return $content;
    }

    public function getHeader() {
        return '<h1>Prenotazione ticket online</h1>';
    }

    public function getTable() {
        $ret = <<<EOS
<table id="listTable">
    <tr>
        <th>Servizio</th>
        <th>Clienti in coda</th>
        <th>Attesa stimata</th>
    </tr>
EOS;
        foreach ( $this->tdList as $td ) {
            $code = $td->getCode();
            $text = $code . " - " . $td->getName();
            $queueLength = Ticket::getNumberTicketInQueue( $code );
            $eta = intval( $td->getEta() / 60 );
            $eta .= " min";

            $row = <<<EOS
    <tr class="clickable" data-code="$code">
        <td>$text</td>
        <td>$queueLength</td>
        <td>$eta</td>
    </tr>
EOS;
            $ret .= $row;
        }
        $ret .= "\n</table>\n";
        return $ret;
    }

}
