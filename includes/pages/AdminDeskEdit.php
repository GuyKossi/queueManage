<?php

/**
 * Description of AdminDeskEdit
 *
 * @author sergio
 */
class AdminDeskEdit extends Page {
    private $message = "";
        
    // Submitted values to show again in the form
    private $desk_id = 0;
    private $desk_number = 0;
    private $desk_ip_address = "";
    private $pairing = 0;
    
    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }
    
    public function afterPermissionCheck() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->desk_id = gfPostVar( 'desk_id', 0 );
            // Keep edited information if present
            $this->desk_number = gfPostVar( 'desk_number', 0 );
            $this->desk_ip_address = gfPostVar( 'desk_ip_address', '' );
            $this->pairing = gfPostVar( 'pairing', 0 );
        } else {
            $this->desk_id = gfGetVar( 'desk_id', 0 );
            if ( $this->desk_id ) {
                $desk = Desk::fromDatabaseById( $this->desk_id );
                if ( $desk !== null ) {
                    $this->desk_number = $desk->getNumber();
                    $this->desk_ip_address = $desk->getIpAddress();
                } else {
                    $this->desk_id = 0;
                }
            }
            $this->pairing = gfGetVar( 'pairing', 0 );
            if ( $this->pairing ) {
                $this->desk_ip_address = $_SERVER['REMOTE_ADDR'];
            }
        }
    }

    public function execute() {
        global $gvPath;
        
        // Trim data
        $this->desk_number = trim( $this->desk_number );
        $this->desk_ip_address = trim( $this->desk_ip_address );
        
        // Data validation
        if ( $this->desk_number === '' && $this->desk_ip_address === '' ) {
            $this->message = "Errore: tutti i campi sono obbligatori.";
            return true;
        }
        
        // desk_number should contain... numbers
        if ( preg_match( '/^[1-9][0-9]*$/', $this->desk_number ) !== 1 ) {
            $this->message = "Errore: il numero dello sportello non è valido.";
            return true;
        }
        
        // Check ip_address
        if ( !filter_var( $this->desk_ip_address, FILTER_VALIDATE_IP ) ) {
            $this->message = "Errore: l'indirizzo IP non è valido.";
            return true;
        }
        
        $desk = Desk::fromDatabaseByNumber( $this->desk_number );
        if (
                $desk &&
                ( $this->desk_id === 0 || $this->desk_id !== (int) $desk->getId() )
        ) {
            $this->message = "Errore: il numero sportello non è disponibile.";
            return true;
        }
        unset( $desk );
        
        // Check ip is not taken
        $desk = Desk::fromDatabaseByIpAddress( $this->desk_ip_address );
        $device = Device::fromDatabaseByIpAddress( $this->desk_ip_address );
        if (
                $device ||
                ( $desk &&
                    ( $this->desk_id === 0 || $this->desk_id !== (int) $desk->getId() )
                )
        ) {
            $this->message = "Errore: l'indirizzo IP è gia stato assegnato.";
            return true;
        }
        unset( $desk );
        
        
        if ( $this->desk_id === 0 ) {
            $desk = Desk::newRecord();
        } else {
            $desk = Desk::fromDatabaseById( $this->desk_id );
        }

        if ( $desk->isOpen() ) {
            $this->message = "Errore: il desk è aperto. Chiudere la sessione prima di continuare.";
            return true;
        }

        $desk->setNumber( $this->desk_number );
        $desk->setIpAddress( $this->desk_ip_address );
        
        if ( $desk->save() ) {
            gfSetDelayedMsg( 'Operazione effettuata correttamente', 'Ok');
            $redirect = new RedirectOutput( "$gvPath/application/adminDeskList" );
            return $redirect;
        } else {
            $this->message = "Impossibile salvare le modifiche. Ritentare in seguito.";
            return true;
        }
        
    }

    public function getOutput() {
        global $gvPath;
        
        $output = new WebPageOutput();
        $output->linkStyleSheet( "$gvPath/assets/css/style.css");
        $output->setHtmlPageTitle( $this->getPageTitle() );
        $output->setHtmlBodyHeader( $this->getPageHeader() );
        $output->setHtmlBodyContent( $this->getPageContent() );
        
        return $output;
    }
    
    private function getPageTitle() {
        if ( $this->desk_id ) {
            return 'Modifica sportello';
        }
        return 'Nuovo sportello';
    }
    
    public function getPageContent() {
        global $gvPath;
        
        $message = $this->message ? "<div class=\"errorMessage\">$this->message</div>" : "";
        
        $ret = <<<EOS
$message
<form action="$gvPath/application/adminDeskEdit" method="post">
	<table>
		<tr>
			<td>Numero:</td>
			<td><input type="number" name="desk_number" id="desk_number" size="40" min="1" max="99" value="$this->desk_number" /></td>
		</tr>
		<tr>
			<td>Indirizzo IP:</td>
			<td><input type="text" name="desk_ip_address" id="desk_ip_address" size="15" value="$this->desk_ip_address" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Salva" /></td>
		</tr>
	</table>
	<input type="hidden" name="desk_id" value="$this->desk_id" />
	<input type="hidden" name="pairing" value="$this->pairing" />
</form>
<p><a href="$gvPath/application/adminDeskList">Torna indietro</a></p>
EOS;
        return $ret;
    }
    
    public function getPageHeader() {
        return "<h1>{$this->getPageTitle()}</h1>";
    }

}
