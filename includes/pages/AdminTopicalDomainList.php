<?php

/**
 * Description of AdminTopicalDomainList
 *
 * @author sergio
 */
class AdminTopicalDomainList extends Page {

    public function canUse( $userLevel ) {
        return $userLevel === Page::SYSADMIN_USER;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function __construct() {
        Database::lockTables( false );
    }

    public function getOutput() {
        global $gvPath;

        $page = new WebPageOutput();
        $page->setHtmlPageTitle( $this->getPageTitle() );
        $page->linkStyleSheet( "$gvPath/assets/css/style.css");
        $page->importJquery();
        $page->addJavascript( "$gvPath/assets/js/recordRemove.js" );
        $page->addJavascript( "$gvPath/assets/js/topicalDomainList.js" );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        
        return $page;
    }
    
    private function getTableBody() {
        global $gvPath;
        
        $topDomains = TopicalDomain::fromDatabaseCompleteList( false );
        
        if ( count( $topDomains ) === 0 ) {
            return '<tr><td colspan="5" class="noEntry">Nessuna area tematica</td></tr>';
        }
        
        $ret = "";
        foreach ( $topDomains as $topDomain ) {
            $checkbox = $this->getCheckbox( $topDomain );
            $ret .= <<<EOS
<tr>
        <td>{$topDomain->getCode()}</td>
        <td>{$topDomain->getName()}</td>
        <td>{$topDomain->getDescription()}</td>
        <td>$checkbox</td>
	<td><a href="$gvPath/application/adminTopicalDomainEdit?td_id={$topDomain->getId()}" class="tdEditLink">Modifica</a>&nbsp;&nbsp;
        <a class="ajaxRemove" href="$gvPath/ajax/removeRecord?td_id={$topDomain->getId()}">Rimuovi</a></td>
</tr>
EOS;
        }
        return $ret;
    }
    
    public function getPageContent() {
        global $gvPath;

        $tableBody = $this->getTableBody();
        $ret = <<<EOS
<table id="listTable">
	<tr>
		<th>Codice</th>
		<th>Nome</th>
		<th>Descrizione</th>
		<th>Attivo?</th>
		<th>Azioni</th>
	</tr>
	$tableBody
</table>
<p><a href="$gvPath/application/adminTopicalDomainEdit">+ Aggiungi area tematica</a></p>
<p><br/><a href="$gvPath/application/adminPage">Torna al menù principale</a></p>
EOS;
        return $ret;
    }
    
    public function getPageTitle() {
        return 'Gestione aree tematiche';
    }
    
    public function getPageHeader() {
        $ret = "<h1>{$this->getPageTitle()}</h1>";
        $ret .= Page::getDelayedMsgBlock();
        return $ret;
    }
    
    private function getCheckbox( $td ) {
        global $gvPath;
        $checkBox = "<input type=\"checkbox\" "
                . "class=\"activateCheckbox\" "
                . "value=\"$gvPath/ajax/activateTopicalDomain?td_id={$td->getId()}\"";
        if ( $td->getActive() ) {
            $checkBox .= " checked";
        }
        $checkBox .= ">";
        return $checkBox;
    }
    
}
