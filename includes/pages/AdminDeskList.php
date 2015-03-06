<?php

/**
 * Description of AdminDeskList
 *
 * @author sergio
 */
class AdminDeskList extends Page {
    
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
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        
        return $page;
    }
    
    private function getTableBody() {
        global $gvPath;
        
        $desks = Desk::fromDatabaseCompleteList();
        
        if ( count( $desks ) === 0 ) {
            return '<tr><td colspan="3" class="noEntry">Nessuno sportello</td></tr>';
        }
        
        $ret = "";
        foreach ( $desks as $desk ) {
            $ret .= <<<EOS
<tr>
        <td>{$desk->getNumber()}</td>
        <td>{$desk->getIpAddress()}</td>
	<td><a href="$gvPath/application/adminDeskEdit?desk_id={$desk->getId()}" class="tdEditLink">Modifica</a>&nbsp;&nbsp;
        <a class="ajaxRemove" href="$gvPath/ajax/removeRecord?desk_id={$desk->getId()}">Rimuovi</a></td>
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
		<th>Numero</th>
		<th>Indirizzo IP</th>
		<th>Azioni</th>
	</tr>
	$tableBody
</table>
<p><a href="$gvPath/application/adminDeskEdit">+ Aggiungi sportello manualmente</a></p>
<p><a href="$gvPath/application/adminDeskEdit?pairing=1">+ Aggiungi questo computer come sportello</a></p>
<p><br/><a href="$gvPath/application/adminPage">Torna al menù principale</a></p>
EOS;
        return $ret;
    }
    
    public function getPageTitle() {
        return 'Gestione sportelli';
    }
    
    public function getPageHeader() {
        $ret = "<h1>{$this->getPageTitle()}</h1>";
        $ret .= Page::getDelayedMsgBlock();
        return $ret;
    }
    
    
    
}
