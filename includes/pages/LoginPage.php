<?php

/**
 * Description of LoginPage
 *
 * @author sergio
 */
class LoginPage extends Page {
    
    // Already escaped
    private $errorMessage;
    
    public function __construct( $errorMessage = false ) {
        $this->setErrorMessage( $errorMessage );
    }


    public function canUse( $userLevel ) {
        // Everyone can login
        return true;
    }
    
    private function isValidSysAdminLogin( $code, $password ) {
        global $gvSysAdminCode, $gvSysAdminPassword;
        
        return $code == $gvSysAdminCode && $password == $gvSysAdminPassword;
    }

    public function execute() {
        global $gvPath;
        
        $code = $_POST['code'];
        $password = $_POST['password'];

        session_destroy();
        unset( $_SESSION );
        session_start();
                
        if ( $this->isValidSysAdminLogin( $code, $password ) ) {
            global $gvEditableConfs;
            $_SESSION['userLevel'] = Page::SYSADMIN_USER;
            if ( $code == $gvEditableConfs[0]->getDefault()
                && $password == $gvEditableConfs[1]->getDefault() ) {
                // Access with default credentials. Redirect to settings.
                $redirect = new RedirectOutput( $gvPath . "/application/adminSettings" );
            } else {
                $redirect = new RedirectOutput( $gvPath . "/application/adminPage" );
            }
            return $redirect;
        }
        
        if ( Operator::isValidLogin( $code, $password ) ) {
            Operator::clearTableForLogout( $code );
            try {
                Session::loginOperator( $code );
            } catch ( UnknownDeskException $e ) {
                global $gvPath;
                $errorPage = new ErrorPageOutput(
                    "Sportello non riconosciuto",
                    "Il presente computer non è stato registrato come sportello.<br />"
                    . "Indirizzo IP da registrare: " . $_SERVER['REMOTE_ADDR'],
                    "<a href=\"$gvPath/application/loginPage\">Torna indietro</a>"
                );
                return $errorPage;
            }
            $redirect = new RedirectOutput( $gvPath . "/application/opPage" );
            return $redirect;
        }
        
        // Login failed
        $this->errorMessage = "Codice o password non validi!";
        return true;
    }

    public function getOutput() {
        global $gvPath;
        
        $output = new WebPageOutput();
        $output->setHtmlPageTitle( "Pagina di log in" );
        $output->linkStyleSheet( "$gvPath/assets/css/normalize.css");
        $output->linkStyleSheet( "$gvPath/assets/css/styleNew.css");
        $output->linkStyleSheet( "$gvPath/assets/css/font-face.css");
        $output->importJquery();
        //$output->addJavascript( $gvPath . "/assets/js/login.js" );
        $output->addJavascript( "$gvPath/assets/js/animationError.js");
        $output->setHtmlBodyHeader( $this->getPageHeader() );
        $output->setHtmlBodyContent( $this->getPageContent() );
        
        return $output;
    }
    
    public function getPageContent() {
        global $gvPath;

        if ( $this->errorMessage ) {
            $message = "<div class=\"error-message\"><p>$this->errorMessage</p></div>";
        } else if ( $delayedText = gfGetDelayedText() ) {
            $message = "<div class=\"error-message\"><p>$delayedText</p></div>";
        } else {
            $message = "";
        }
        
        $return = <<<EOS
<div class="root">
	<div class="container">
		<div class="login-container">
		<div class="login-title">logo - Queue</div>
		<form action ="$gvPath/application/loginPage" method="post" autocomplete="off">
			<input type="text" name="code" id="code" placeholder="Codice">
			<input type="password" name="password" id="password" placeholder="Password">
			<input class ="test" type="submit" value="Log in" id="loginSubmit">
		</form>
		$message
		</div>
	</div>
</div>
EOS;
        
        return $return;
    }

    public function getPageHeader() {
        return '';
    }
    
    public function setErrorMessage( $message, $append = false ) {
        $escaped = htmlspecialchars( $message );
        if ( $append ) {
            $this->errorMessage .= $escaped;
        } else {
            $this->errorMessage = $escaped;
        }
    }
}
