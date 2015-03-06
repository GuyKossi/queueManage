<?php

/**
 * Description of AdminPage
 *
 * @author sergio
 */
class AdminPage extends Page {
    
    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $page = new WebPageOutput();
        $page->setHtmlPageTitle( 'Pannello amministrazione FastQueue' );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        $page->setHtmlBodyFooter( $this->getPageFooter() );
        
        return $page;
    }
    
    public function getPageContent() {
        global $gvPath;

        $ret = <<<EOS
<p>Benvenuto nel pannello di gestione di FastQueue.</p>
<p>Seleziona una voce nel menù seguente per continuare</p>
<ul>
   <li><a href="$gvPath/application/adminOperatorList">Operatori</a></li>
   <li><a href="$gvPath/application/adminDeskList">Sportelli</a></li>
   <li><a href="$gvPath/application/adminTopicalDomainList">Aree tematiche</a></li>
   <li><a href="$gvPath/application/adminDeviceList">Dispositivi</a></li>
   <li><a href="$gvPath/application/adminStats">Statistiche</a></li>
   <li><a href="$gvPath/application/adminSettings">Impostazioni</a></li>
</ul>
EOS;
        return $ret;
    }
    
    public function getPageHeader() {
        $ret = "<h1>Pannello amministrazione FastQueue</h1>";
        return $ret;
    }

    public function getPageFooter() {
        global $gvPath;
        return "<br /><a href=\"$gvPath/application/logoutPage\">Logout</a>";
    }

}
