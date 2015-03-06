<?php

/**
 * Description of GsHomePage
 *
 * @author sergio
 */
class GsHomePage extends Page {

	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $output = new WebPageOutput();
        $output->setHtmlPageTitle( $this->getTitle() );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        global $gvPath;
        $ret = <<<EOS
<form action="$gvPath/globalServer/search" method="get">
<label for="q">Inserire la città</label><br />
<input type="text" name="q" size="25" required /><br />
<input type="submit" value="Cerca" />
</form>
EOS;
        return $ret;
    }

    public function getHeader() {
        return "<h1>{$this->getTitle()}</h1>";
    }

    public function getTitle() {
        return 'Cerca l\'ufficio più vicino';
    }

}
