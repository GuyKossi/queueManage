<?php

/**
 * Description of DeviceDisplayMain
 *
 * @author sergio
 */
class DeviceDisplayMain extends Page {

	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvPath;

        $output = new WebPageOutput();
        $output->setHtmlPageTitle( 'DisplayMain' );
        $output->importJquery();
        $output->addJavascript( "$gvPath/assets/js/displayMain.js" );
        $output->linkStyleSheet( "$gvPath/assets/css/normalize.css" );
        $output->linkStyleSheet( "$gvPath/assets/css/style.css" );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        $content = <<<EOS
<div class="no-entry">Nessun numero servito</div>
EOS;
        return $content;
    }

}
