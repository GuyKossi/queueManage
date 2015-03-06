<?php

/**
 * Description of AdminDeviceList
 *
 * @author sergio
 */
class AdminDeviceList extends Page {
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
        
        $devices = Device::fromDatabaseCompleteList();
        
        if ( count( $devices ) === 0 ) {
            return '<tr><td colspan="4" class="noEntry">Nessun dispositivo</td></tr>';
        }
        
        $ret = "";
        foreach ( $devices as $device ) {
            if ( $device->getDeskNumber() ) {
                $roleMsg = "Display spotello " . $device->getDeskNumber();
                $tdText = '&nbsp;';
            } else {
                $roleMsg = "Display di sala";
                if ( $device->getTdCode() ) {
                    $tdText = $device->getTdCode();
                } else {
                    $tdText = 'Tutte';
                }
            }

            $ret .= <<<EOS
<tr>
        <td>{$device->getIpAddress()}</td>
        <td>$roleMsg</td>
        <td>$tdText</td>
        <td><a href="$gvPath/application/adminDeviceEdit?dev_id={$device->getId()}" class="tdEditLink">Modifica</a>&nbsp;&nbsp;
        <a class="ajaxRemove" href="$gvPath/ajax/removeRecord?dev_id={$device->getId()}">Rimuovi</a></td>
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
		<th>Indirizzo IP</th>
		<th>Funzione</th>
        <th>Aree tematiche</th>
		<th>Azioni</th>
	</tr>
	$tableBody
</table>
<p><a href="$gvPath/application/adminDeviceEdit">+ Aggiungi Dispositivo</a></p>
<p><br/><a href="$gvPath/application/adminPage">Torna al menù principale</a></p>
EOS;
        return $ret;
    }
    
    public function getPageTitle() {
        return 'Gestione dispositivi';
    }
    
    public function getPageHeader() {
        $ret = "<h1>{$this->getPageTitle()}</h1>";
        $ret .= Page::getDelayedMsgBlock();
        return $ret;
    }
}
