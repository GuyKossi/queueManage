<?php

/**
 * Description of AdminDeviceEdit
 *
 * @author sergio
 */
class AdminDeviceEdit extends Page {

    private $message = "";
        
    // Submitted values to show again in the form
    private $dev_id = 0;
    private $dev_ip_address = '';
    private $dev_desk_number = "";
    private $dev_td_code = null;
    
    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }
    
    public function afterPermissionCheck() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->dev_id = gfPostVar( 'dev_id', 0 );
            // Keep edited information if present
            $this->dev_ip_address = gfPostVar( 'dev_ip_address', '' );
            $this->dev_desk_number = gfPostVar( 'dev_desk_number', 0 );
            $this->dev_td_code = gfPostVar( 'dev_td_code', '0' );
        } else {
            $this->dev_id = gfGetVar( 'dev_id', 0 );
            if ( $this->dev_id ) {
                $device = Device::fromDatabaseById( $this->dev_id );
                if ( $device !== null ) {
                    $this->dev_ip_address = $device->getIpAddress();
                    $this->dev_desk_number = $device->getDeskNumber();
                    $this->dev_td_code = $device->getTdCode();
                } else {
                    $this->dev_id = 0;
                }
            }
        }

        if ( $this->dev_td_code == '0' || $this->dev_desk_number != 0 ) {
            $this->dev_td_code = null;
        }
    }

    public function execute() {
        global $gvPath;
        
        // Trim data
        $this->dev_ip_address = trim( $this->dev_ip_address );
        $this->dev_desk_number = trim( $this->dev_desk_number );
        
        // Data validation
        if ( $this->dev_ip_address === '' || $this->dev_desk_number === '' ) {
            $this->message = "Errore: tutti i campi sono obbligatori.";
            return true;
        }
        
        // dev_desk_number should contain... numbers
        if ( preg_match( '/^(0|[1-9][0-9]*)$/', $this->dev_desk_number ) !== 1 ) {
            $this->message = "Errore: il numero dello sportello non è valido.";
            return true;
        }
        
        // Check ip_address
        if ( !filter_var( $this->dev_ip_address, FILTER_VALIDATE_IP ) ) {
            $this->message = "Errore: l'indirizzo IP non è valido.";
            return true;
        }
        
        // Check if desk number really exists
        if ( (int) $this->dev_desk_number !== 0 ) {
            $desk = Desk::fromDatabaseByNumber( $this->dev_desk_number );
            if ( !$desk ) {
                $this->message = "Errore: lo sportello specificato non esiste.";
                return true;
            }
            unset( $desk );
        }

        // Check tdCode exists and active
        if ( $this->dev_td_code ) {
            $td = TopicalDomain::fromDatabaseByCode( $this->dev_td_code );
            if ( !$td || !$td->getActive() ) {
                $this->message = "Errore: l'area tematica selezionata non è disponibile.";
                return true;
            }
        }
        
        // Check ip is not taken
        $device = Device::fromDatabaseByIpAddress( $this->dev_ip_address );
        $desk = Desk::fromDatabaseByIpAddress( $this->dev_ip_address );
        if (
                $desk ||
                ( $device &&
                    ( $this->dev_id === 0 || $this->dev_id !== (int) $device->getId() )
                )
        ) {
            $this->message = "Errore: l'indirizzo IP è gia stato assegnato.";
            return true;
        }
        unset( $device );
        
        if ( $this->dev_id === 0 ) {
            $device = Device::newRecord();
        } else {
            $device = Device::fromDatabaseById( $this->dev_id );
        }
        $device->setIpAddress( $this->dev_ip_address );
        $device->setDeskNumber( $this->dev_desk_number );
        $device->setTdCode( $this->dev_td_code );
        
        if ( $device->save() ) {
            gfSetDelayedMsg( 'Operazione effettuata correttamente', 'Ok');
            $redirect = new RedirectOutput( "$gvPath/application/adminDeviceList" );
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
        $output->importJquery();
        $output->addJavascript( "$gvPath/assets/js/adminDeviceEdit.js");
        $output->setHtmlPageTitle( $this->getPageTitle() );
        $output->setHtmlBodyHeader( $this->getPageHeader() );
        $output->setHtmlBodyContent( $this->getPageContent() );
        
        return $output;
    }
    
    private function getPageTitle() {
        if ( $this->dev_id ) {
            return 'Modifica dispositivo';
        }
        return 'Nuovo dispositivo';
    }
    
    public function getPageContent() {
        global $gvPath;
        
        $message = $this->message ? "<div class=\"errorMessage\">$this->message</div>" : "";
        $combobox = $this->getCombobox();
        $comboboxTd = $this->getComboboxTd();

        $ret = <<<EOS
$message
<form action="$gvPath/application/adminDeviceEdit" method="post">
	<table>
		<tr>
            <td>Indirizzo IP:</td>
			<td><input type="text" name="dev_ip_address" id="dev_ip_address" size="15" value="$this->dev_ip_address" /></td>
		</tr>
		<tr>
            <td>Funzionalità:</td>
			<td>
                $combobox
            </td>
		</tr>
        <tr>
            <td>Area tematica:</td>
            <td>
                $comboboxTd
            </td>
        </tr>
		<tr>
			<td colspan="2"><input type="submit" value="Salva" /></td>
		</tr>
	</table>
	<input type="hidden" name="dev_id" value="$this->dev_id" />
</form>
<p><a href="$gvPath/application/adminDeviceList">Torna indietro</a></p>
EOS;
        return $ret;
    }
    
    public function getPageHeader() {
        return "<h1>{$this->getPageTitle()}</h1>";
    }

    private function getCombobox() {
        $ret = '<select name="dev_desk_number" id="dev_desk_number">';
        $ret .= PHP_EOL . '<option value="0">Display di sala</option>';
        foreach ( Desk::getUsedDeskNumbers() as $num ) {
            $selected = $this->dev_desk_number === $num ? ' selected' : '';
            $ret .= "\n<option value=\"$num\"$selected>Display sportello $num</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }

    private function getComboboxTd() {
        $ret = '<select name="dev_td_code" id="dev_td_code">';
        $ret .= PHP_EOL . '<option value="0">Tutte</option>';
        foreach ( TopicalDomain::fromDatabaseCompleteList() as $td ) {
            $code = $td->getCode();
            $selected = $this->dev_td_code === $code ? ' selected' : '';
            $ret .= "\n<option value=\"$code\"$selected>$code</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }

}
