<?php

/**
 * Description of AdminSettings
 *
 * @author sergio
 */
class AdminSettings extends Page {

    public $message = '';

    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }

    public function afterPermissionCheck() {
        // Set message when being redirected from loginPage
        if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
            $referer = $_SERVER['HTTP_REFERER'];
            if ( false !== strpos( $referer, 'loginPage') ) {
                $this->message = 'È stato effettuato l\'accesso al sistema con le credenziali ' .
                    'di default.<br />Si prega di modificarle prima di continuare.';
            }
        }
    }

    public function execute() {
        global $gvEditableConfs, $gvDirectory;

        $modifiedConfs = array();
        foreach ( $gvEditableConfs as $conf ) {
            if ( !isset( $_POST[$conf->getName()] ) ) {
                // Checkboxes not checked are not sent via Post
                if ( $conf->getType() == 'boolean' ) {
                    $_POST[$conf->getName()] = 0;
                } else {
                    // Something went wrong, abort execution
                    $this->message = "Errore nel processare la richiesta. Riprovare in seguito.";
                    return true;
                }
            }
            $newValue = $_POST[$conf->getName()];
            // Empty value means default
            if ( $newValue === '' ) {
                $newValue = $conf->getDefault();
            }
            $conf->setNewValue( $newValue );
            $modifiedConfs[] = $conf;
        }

        $generator = new LocalSettingsGenerator( $modifiedConfs );
        if ( $generator->writeFile( "$gvDirectory/LocalSettings.php" ) ) {
            global $gvPath;

            // Use new settings asap
            foreach ( $modifiedConfs as $conf ) {
                $conf->exportNewValue();
            }

            $this->message = "Configurazione salvata correttamente.<br />\n";
            $this->message .= "È possibile tornare al <a href=\"$gvPath/application/adminPage\">menù principale</a>.";
            return true;
        } else {
            $this->message = "Errore nel salvataggio del file. Controllare i permessi di scrittura.";
            return true;
        }
    }

    public function getOutput() {
        $page = new WebPageOutput();
        $page->setHtmlPageTitle( $this->getTitle() );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        $page->setHtmlBodyFooter( $this->getPageFooter() );
        
        return $page;
    }
    
    public function getPageContent() {
        global $gvPath;

        $message = $this->message ? "<h3>$this->message</h3>" : '';

        $form = $this->getForm();
        $ret = <<<EOS
$message
<p>In questa pagina è possibile modificare alcune impostazioni interne del software.</p>
<p>Lasciare il campo vuoto per reimpostare il valore di default.</p>
$form
EOS;
        return $ret;
    }

    public function getForm() {
        global $gvEditableConfs;

        $fields = '';
        foreach ( $gvEditableConfs as $conf ) {
            $tag = $this->generateInputTag( $conf );
            $fields .= <<<EOS
<tr>
    <td>{$conf->getText()}</td>
    <td>
        $tag
    </td>
</tr>\n
EOS;
        }

        $form = <<<EOS
<form method="post">
<table>
$fields
<tr>
    <td colspan="2">
        <input type="submit" value="Salva">
    </td>
<tr>
</table>
</form>
EOS;
        return $form;
    }

    protected function generateInputTag( $conf ) {
        $tagName = 'input';
        $attributes = '';
        $value = $GLOBALS[$conf->getName()];
        $type = $conf->getType();
        if ( $type == 'integer' ) {
            $attributes .= ' type="number"';
        } elseif ( $type == 'boolean' ) {
            $attributes .= ' type="checkbox"';
            if ( $value ) {
                $attributes .= ' checked';
            }
            // Checkboxes should always contain 'true' values
            $value = 1;
        } elseif ( $type == 'textarea' ) {
            $tagName = 'textarea';
            $attributes .= ' cols="20" rows="6"';
        } else {
            $attributes .= ' type="text" size="20"';
        }
        if ( $tagName == 'input' ) {
            return "<input name=\"{$conf->getName()}\" value=\"$value\"$attributes />";
        } else {
            return "<textarea name=\"{$conf->getName()}\"$attributes>$value</textarea>";
        }
    }
    
    public function getPageHeader() {
        return "<h1>{$this->getTitle()}</h1>";
    }

    public function getTitle() {
        return 'Impostazioni';
    }

    public function getPageFooter() {
        global $gvPath;
        return "<br /><a href=\"$gvPath/application/adminPage\">Torna al menu principale</a>";
    }

}
