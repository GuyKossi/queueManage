<?php

/**
 * Description of AdminTopicalDomainEdit
 *
 * @author sergio
 */
class AdminTopicalDomainEdit extends Page {
    private $message = "";
    
    // Submitted values to show again in the form
    private $td_id = 0;
    private $td_code = "";
    private $td_name = "";
    private $td_description = "";
    private $td_icon = 0;
    private $td_color = 0;
    
    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }
    
    public function __construct() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->td_id = gfPostVar( 'td_id', 0 );
            // Keep edited information if present
            $this->td_code = gfPostVar( 'td_code', '' );
            $this->td_name = gfPostVar( 'td_name', '' );
            $this->td_description = gfPostVar( 'td_description', '' );
            $this->td_icon = gfPostVar( 'td_icon', 0 );
            $this->td_color = gfPostVar( 'td_color', 0 );
        } else {
            $this->td_id = gfGetVar( 'td_id', 0 );
            if ( $this->td_id ) {
                $td = TopicalDomain::fromDatabaseById( $this->td_id );
                if ( $td !== null ) {
                    $this->td_code = $td->getCode();
                    $this->td_name = $td->getName();
                    $this->td_description = $td->getDescription();
                    $this->td_icon = (int) $td->getIcon();
                    $this->td_color = (int) $td->getColor();
                } else {
                    $this->td_id = 0;
                }
            }
        }
    }
    
    public function execute() {
        
        // Trim data
        $this->td_name = trim( $this->td_name );
        $this->td_description = trim( $this->td_description );
        
        // Data validation
        if ( $this->td_name === '' ) {
            $this->message = "Errore: il campo nome è obbligatorio.";
            return true;
        }
        
        // Sanitize td_name
        if ( preg_match( '/^[0-9a-zàèéìò \']+$/i', $this->td_name ) !== 1 ) {
            $this->message = "Errore: il nome contiene caratteri non validi.";
            return true;
        }
        
        // Sanitize td_description
        if ( preg_match( '/^[0-9a-zàèéìò \'.,();:"]*$/i', $this->td_description ) !== 1 ) {
            $this->message = "Errore: la descrizione contiene caratteri non validi.";
            return true;
        }
        
        // Check that topical domain is disabled
        // Hopefully this has already been done before ;-)
        if ( $this->td_id ) {
            $td = TopicalDomain::fromDatabaseById( $this->td_id );
            if ( $td->getActive() ) {
                $this->message = "Errore: l'area tematica non è disattivata.";
                return true;
            }
        }
        
        
        if ( $this->td_id === 0 ) {
            $td = TopicalDomain::newRecord();
            $td->setActive( 1 );
        } else {
            $td = TopicalDomain::fromDatabaseById( $this->td_id );
        }
        $td->setCode( $this->td_code );
        $td->setName( $this->td_name );
        $td->setDescription( $this->td_description );
        $td->setIcon( $this->td_icon );
        $td->setColor( $this->td_color );
            
        if ( $td->save() ) {
            gfSetDelayedMsg( 'Operazione effettuata correttamente', 'Ok');
            global $gvPath;
            $redirect = new RedirectOutput( "$gvPath/application/adminTopicalDomainList" );
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
        if ( $this->td_id ) {
            return 'Modifica area tematica';
        }
        return 'Nuova area tematica';
    }
    
    public function getPageContent() {
        global $gvPath;
        
        $message = $this->message ? "<div class=\"errorMessage\">$this->message</div>" : "";
        $codeCombobox = $this->getComboBoxForCode();
        $iconCombobox = $this->getComboBoxForIcon();
        $colorCombobox = $this->getComboBoxForColor();
        $ret = <<<EOS
$message
<form action="$gvPath/application/adminTopicalDomainEdit" method="post">
	<table>
		<tr>
			<td>Codice:</td>
			<td>
				$codeCombobox
			</td>
		</tr>
		<tr>
			<td>Nome:</td>
			<td><input type="text" name="td_name" id="td_name" size="40" value="$this->td_name" /></td>
		</tr>
		<tr>
			<td>Descrizione:</td>
			<td>
				<textarea rows="5" cols="40" name="td_description" id="td_description">$this->td_description</textarea>
			</td>
		</tr>
		<tr>
			<td>Icona:</td>
			<td>
				$iconCombobox
			</td>
		</tr>
		<tr>
			<td>Colore:</td>
			<td>
				$colorCombobox
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Salva" /></td>
		</tr>
	</table>
	<input type="hidden" name="td_id" value="$this->td_id" />
</form>
<p><a href="$gvPath/application/adminTopicalDomainList">Torna indietro</a></p>
EOS;
        return $ret;
    }
    
    private function getComboBoxForCode() {
        $ret = '<select name="td_code" id="td_code">';
        $availableCodes = TopicalDomain::getAvailableCodes();
        if ( $this->td_code ) {
            // Let be sure to always list the actual code
            if ( !in_array( $this->td_code, $availableCodes ) ) {
                $availableCodes[] = $this->td_code;
                sort($availableCodes);
            }
        }
        foreach ( $availableCodes as $code ) {
            $selected = $this->td_code === $code ? ' selected' : '';
            $ret .= "\n<option value=\"$code\"$selected>$code</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }
    
    private function getComboBoxForIcon() {
        $ret = '<select name="td_icon" id="td_icon">';
        foreach ( TopicalDomain::$ICONS as $index => $icon ) {
            $selected = $this->td_icon === $index ? ' selected' : '';
            if ( $index === 0 ) {
                $text = "Nessuna icona";
            } else {
                $text = $icon[0];
            }
            $ret .= "\n<option value=\"$index\"$selected>$text</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }
    
    private function getComboBoxForColor() {
        $ret = '<select name="td_color" id="td_color">';
        foreach ( TopicalDomain::$COLORS as $index => $color ) {
            $selected = $this->td_color === $index ? ' selected' : '';
            if ( $index === 0 ) {
                $text = "Nessun colore";
            } else {
                $text = $color[0];
            }
            $ret .= "\n<option value=\"$index\"$selected>$text</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }
    
    public function getPageHeader() {
        return "<h1>{$this->getPageTitle()}</h1>";
    }
}
