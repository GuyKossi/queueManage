<?php

/**
 * Description of AdminOperatorEdit
 *
 * @author sergio
 */
class AdminOperatorEdit extends Page {
    private $message = "";
        
    // Submitted values to show again in the form
    private $op_id = 0;
    private $op_code = "";
    private $op_name = "";
    private $op_surname = "";
    
    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }
    
    public function afterPermissionCheck() {
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->op_id = gfPostVar( 'op_id', 0 );
            // Keep edited information if present
            $this->op_code = gfPostVar( 'op_code', '' );
            $this->op_name = gfPostVar( 'op_name', '' );
            $this->op_surname = gfPostVar( 'op_surname', '' );
        } else {
            $this->op_id = gfGetVar( 'op_id', 0 );
            if ( $this->op_id ) {
                $op = Operator::fromDatabaseById( $this->op_id );
                if ( $op !== null ) {
                    $this->op_code = $op->getCode();
                    $this->op_name = $op->getName();
                    $this->op_surname = $op->getSurname();
                } else {
                    $this->op_id = 0;
                }
            }
        }
    }

    public function execute() {
        global $gvMinPasswordLength, $gvPath;
        
        $op_password = gfPostVar( 'op_password', '' );
        $op_password_repete = gfPostVar( 'op_password_repete', '' );
        
        // Trim data
        $this->op_code = trim( $this->op_code );
        $this->op_name = trim( $this->op_name );
        $this->op_surname = trim( $this->op_surname );
        
        // Data validation
        if (
            $this->op_code === ''
            || $this->op_name === ''
            || $this->op_surname === ''
        ) {
            $this->message = "Errore: tutti i campi sono obbligatori.";
            return true;
        }
        
        if ( $this->op_id === 0 && $op_password === '') {
            $this->message = "Errore: il campo password è obbligatorio.";
            return true;
        }
        
        if ( $op_password && strlen( $op_password ) < $gvMinPasswordLength ) {
            $this->message = "Errore: la password deve contenere almeno "
                    . "$gvMinPasswordLength caratteri.";
            return true;
        }
        
        if ( $op_password !== $op_password_repete ) {
            $this->message = "Errore: le password non coincidono.";
            return true;
        }
        
        // Allow only letters and digits in op_code
        if ( preg_match( '/^[0-9a-z]+$/i', $this->op_code ) !== 1 ) {
            $this->message = "Errore: il codice operatore non è valido.";
            return true;
        }
        
        // Check name
        if ( preg_match( '/^[a-z \'àèéìòù]+$/i', $this->op_name ) !== 1 ) {
            $this->message = "Errore: il nome contiene caratteri non validi.";
            return true;
        }
        
        // Check surname
        if ( preg_match( '/^[a-z \'àèéìòù]+$/i', $this->op_surname ) !== 1 ) {
            $this->message = "Errore: il cognome contiene caratteri non validi.";
            return true;
        }
        
        // Check if code is taken for new operator
        $op = Operator::fromDatabaseByCode( $this->op_code );
        if (
                $op &&
                ( $this->op_id === 0 || $this->op_id !== (int) $op->getId() )
        ) {
            $this->message = "Errore: il codice operatore non è disponibile.";
            return true;
        }
        unset( $op );
        
        // Check operator is offline (only when edit)
        if ( $this->op_id !== 0 ) {
            $operator = Operator::fromDatabaseById( $this->op_id );
            if ( !$operator ) {
                $this->message = "Errore interno: il record non è presente.";
                return true;
            }
            if ( $operator->isOnline() ) {
                $this->message = "L'operatore è online, impossibile modificarlo.";
                return true;
            }
        }
        
        if ( $this->op_id === 0 ) {
            $op = Operator::newRecord();
            $op->setCode( $this->op_code );
            $op->setName( $this->op_name );
            $op->setSurname( $this->op_surname );
            $op->setPassword( $op_password );
        } else {
            $op = Operator::fromDatabaseById( $this->op_id );
            $op->setCode( $this->op_code );
            $op->setName( $this->op_name );
            $op->setSurname( $this->op_surname );
            if ( $op_password ) {
                $op->setPassword( $op_password );
            }
        }
        
        if ( $op->save() ) {
            gfSetDelayedMsg( 'Operazione effettuata correttamente', 'Ok');
            $redirect = new RedirectOutput( "$gvPath/application/adminOperatorList" );
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
        if ( $this->op_id ) {
            return 'Modifica operatore';
        }
        return 'Nuovo operatore';
    }
    
    public function getPageContent() {
        global $gvPath;
        
        if ( $this->op_id ) {
            $p = "<p>Se non si desidera cambiare la password, lasciare i campi vuoti.</p>";
        } else {
            $p = "";
        }
        
        $message = $this->message ? "<div class=\"errorMessage\">$this->message</div>" : "";
        
        $ret = <<<EOS
$message
$p
<form action="$gvPath/application/adminOperatorEdit" method="post">
	<table>
		<tr>
			<td>Codice:</td>
			<td><input type="text" name="op_code" id="op_code" size="40" value="$this->op_code" /></td>
		</tr>
		<tr>
			<td>Nome:</td>
			<td><input type="text" name="op_name" id="op_name" size="40" value="$this->op_name" /></td>
		</tr>
        <tr>
            <td>Cognome:</td>
            <td><input type="text" name="op_surname" id="op_surname" size="40" value="$this->op_surname" /></td>
        </tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="op_password" id="op_password" size="40" /></td>
		</tr>
		<tr>
			<td>Ripeti password:</td>
			<td><input type="password" name="op_password_repete" id="op_password_repete" size="40" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Salva" /></td>
		</tr>
	</table>
	<input type="hidden" name="op_id" value="$this->op_id" />
</form>
<p><a href="$gvPath/application/adminOperatorList">Torna indietro</a></p>
EOS;
        return $ret;
    }
    
    public function getPageHeader() {
        return "<h1>{$this->getPageTitle()}</h1>";
    }

}
