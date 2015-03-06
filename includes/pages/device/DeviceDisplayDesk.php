<?php

/**
 * Description of DeviceDisplayDesk
 *
 * @author sergio
 */
class DeviceDisplayDesk extends Page {

	private $desk_number;
	private $ticket = null;

	public function afterPermissionCheck() {
		$this->desk_number = (int) gfGetVar( 'desk_number' );

		if ( $this->desk_number ) {
			$this->ticket = Ticket::fromDatabaseByDesk( $this->desk_number );
		}

	}
    
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvPath;

        if ( !$this->desk_number ) {
        	// Something is wrong with the desk number
        	// Do not redirect back to /device page otherwise
        	// redirect loop may happen
        	$redirect = new RedirectOutput( "$gvPath/application/loginPage" );
            return $redirect;
        }

        $content = $this->getPageContent();
        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'DisplayDesk' );
        $output->importJquery();
        $output->addJavascript( "$gvPath/assets/js/displayDesk.js" );
        $output->setHtmlBodyContent( $this->getPageContent() );
        return $output;
    }

    private function getPageContent() {

    	if ( !$this->ticket ) {
    		$text = "Chiuso";
    		$code = '';
    		$number = '';
    	} else {
    		$text = $this->ticket->getTextString();
    		$code = $this->ticket->getCode();
    		$number = $this->ticket->getNumber();
    	}

    	$ret = <<<EOS
<div style="display: none;">
  <div id="displayTicketCode">$code</div>
  <div id="displayTicketNumber">$number</div>
  <div id="displayTicketDesk">$this->desk_number</div>
</div>
<div id="servedNumber">
$text
</div>
EOS;

		return $ret;
    }

}
