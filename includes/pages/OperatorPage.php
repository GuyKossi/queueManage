<?php

/**
 * Description of OperatorPage
 *
 * @author sergio
 */
class OperatorPage extends Page {

    private $operator = null;
    private $desk = null;
    private $ticket_served = null;
    private $td_served = array();
    private $message = '';
    private $pauseButtonEnabled = true;
    private $disableNextButton = false;
    
    public function canUse( $userLevel ) {
        return $userLevel == Page::OPERATOR_USER;
    }

    public function execute() {
        $this->td_served = gfPostVar( 'td_served', array() );
        $_SESSION['td_served'] = $this->td_served;

        if ( !$this->td_served && !isset( $_POST['pause'] ) ) {
            $this->message = "Errore: selezionare almeno un'area tematica.";
            return true;
        }

        // Handle served ticket
        $served = Ticket::fromDatabaseByDesk( $this->getDesk()->getNumber() );
        if ( $served ) {
            $stats = TicketStats::newFromTicket( $served );
            $served->delete();
            if ( !$stats->save() ) {
                throw new Exception( "Unable to save ticket stats." );
            }
        }
        
        // Handle pause button
        if ( isset( $_POST['pause'] ) ) {
            $this->pauseButtonEnabled = false;
            $this->ticket_served = null;
            return true;
        }

        // Call next ticket
        $ticket = Ticket::serveNextTicket(
            $this->td_served,
            $this->getOperator()->getCode(),
            $this->getDesk()->getNumber()
        );
        if ( !$ticket ) {
            $this->message = "Nessun ticket da chiamare";
            $this->pauseButtonEnabled = false;
            $this->ticket_served = null;
            return true;
        }
        $ticket->save();
        $this->ticket_served = $ticket;
        $this->disableNextButton = true;
        $this->pauseButtonEnabled = true;
        return true;
    }
    
    public function afterPermissionCheck() {
        $this->td_served = gfSessionVar( 'td_served', array() );
        $this->ticket_served = Ticket::fromDatabaseByOperator(
                $this->getOperator()->getCode()
        );
        if ( $this->ticket_served == null ) {
            $this->pauseButtonEnabled = false;
        } else {
            $this->disableNextButton = true;
        }
    }

    public function getOutput() {
        global $gvPath;

        $page = new WebPageOutput();
        $page->setHtmlPageTitle( "Pagina operatore" );
        $page->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $page->importJquery();
        $page->addJavascript( "$gvPath/assets/js/opPage.js" );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        return $page;
    }
    
    private function getPageContent() {
        global $gvAllowPause;

        $pMessage = $this->message ? "<p>{$this->message}</p>" : '';
        $servedText = $this->ticket_served ? $this->ticket_served->getTextString() : 'Nessuno';
        $tableBody = $this->getTableBody();
        $nextButtonBlock = $this->getHiddenForNextButton();

        $ret = <<<EOS
$nextButtonBlock
<form method="post">
<div>
    <input type="submit" name="next" value="Prossimo">\n
EOS;
        if ( $gvAllowPause ) {
            $disabled = $this->pauseButtonEnabled ?
                '' : ' disabled';
            $ret .= '&nbsp;&nbsp;<input type="submit" name="pause" value="Pausa"' . $disabled . '>' . PHP_EOL;
        }
$ret .= <<<EOS
    $pMessage
    <p>Numero servito: {$servedText}</p>
</div>
<p>Selezionare le aree tematiche da servire:</p>
<table>
    $tableBody
</table>
</form>
EOS;
return $ret;
    }
    
    private function getPageHeader() {
        global $gvPath;
        if ( $this->td_served ) {
            $tdServedText = implode( ', ', $this->td_served );
        } else {
            $tdServedText = 'Nessuna';
        }

        $ret = <<<EOS
<div>
    Nome operatore: {$this->getOperator()->getFullName()}<br />
    Codice: {$this->getOperator()->getCode()}<br />
    Sportello: {$this->getDesk()->getNumber()}<br />
    Aree servite: $tdServedText<br />
    <a href="$gvPath/application/help">Aiuto</a><br />
    <a href="$gvPath/application/logoutPage">Logout</a>
    <h1>Pagina operatore</h1>
</div>
EOS;
    return $ret;
    }

    private function getHiddenForNextButton() {
        $ret = '<div id="disableNextButton" style="display: none;">' . PHP_EOL;
        if ( $this->disableNextButton ) {
            $ret .= "true\n";
        } else {
            $ret .= "false\n";
        }
        $ret .= "</div>\n";
        return $ret;
    }

    private function getOperator() {
        if ( !$this->operator ) {
            // Get from session
            if ( isset( $_SESSION['operator'] ) ) {
                $this->operator = $_SESSION['operator'];
            } else if ( isset( $_SESSION['op_code'] ) ) {
                $this->operator = Operator::fromDatabaseByCode( $_SESSION['op_code'] );
            } else {
                throw new Exception( "Unable to retrieve logged-in operator." );
            }
        }
        return $this->operator;
    }

    private function getDesk() {
        if ( !$this->desk ) {
            if ( isset( $_SESSION['desk'] ) ) {
                $this->desk = $_SESSION['desk'];
            } else if ( isset( $_SESSION['desk_number'] ) ) {
                $this->desk = Desk::fromDatabaseByNumber( $_SESSION['desk_number'] );
            } else {
                throw new Exception( "Unable to retrieve operator's desk." );
            }
        }
        return $this->desk;
    }
    
    private function getCheckBox( $td_code, $text, $queueLength, $checked = false ) {
        $checked = $checked ? ' checked' : '';
        return "<input type=\"checkbox\" name=\"td_served[]\" value=\"$td_code\"$checked />&nbsp;$text&nbsp;($queueLength)";
    }

    private function getTableBody() {
        $topicalDomains = TopicalDomain::fromDatabaseCompleteList( true );
        $tableBody = '';
        for ( $i = 0; $i < count( $topicalDomains ); $i++ ) {
            $td = $topicalDomains[$i];
            $description = $td->getCode() . " - " . htmlspecialchars( $td->getName() ) ;
            $queueLength = Ticket::getNumberTicketInQueue( $td->getCode() );
            $checkbox = $this->getCheckBox(
                $td->getCode(),
                $description,
                $queueLength,
                in_array( $td->getCode() , $this->td_served )
            );
            if ( $i % 4 === 0 ) {
                $tableBody .= "<tr>\n";
            }
            $tableBody .= "<td style=\"padding: 5px;\">$checkbox</td>\n";
            if ( ( $i + 1 ) % 4 === 0 ) {
                $tableBody .= "</tr>\n";
            }
        }
        // Close row if not closed
        if ( substr( $tableBody, -6 ) !== "</tr>\n") {
            $tableBody .= "</tr>\n";
        }
        return $tableBody;
    }

}
