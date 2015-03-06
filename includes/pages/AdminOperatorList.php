<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adminOperatorList
 *
 * @author sergio
 */
class AdminOperatorList extends Page {
    
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
        $page->setHtmlPageTitle( "Gestione operatori" );
        $page->linkStyleSheet( "$gvPath/assets/css/style.css");
        $page->importJquery();
        $page->addJavascript( "$gvPath/assets/js/recordRemove.js" );
        $page->setHtmlBodyHeader( $this->getPageHeader() );
        $page->setHtmlBodyContent( $this->getPageContent() );
        
        return $page;
    }
    
    private function getTableBody() {
        global $gvPath;
        
        $operators = Operator::fromDatabaseCompleteList();
        
        if ( count( $operators ) === 0 ) {
            return '<tr><td colspan="3" class="noEntry">Nessun operatore</td></tr>';
        }
        
        $ret = "";
        foreach ( $operators as $operator ) {
            $ret .= <<<EOS
<tr>
        <td>{$operator->getCode()}</td>
        <td>{$operator->getFullName()}</td>
	<td><a href="$gvPath/application/adminOperatorEdit?op_id={$operator->getId()}">Modifica</a>&nbsp;&nbsp;
        <a class="ajaxRemove" href="$gvPath/ajax/removeRecord?op_id={$operator->getId()}">Rimuovi</a></td>
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
		<th>Azioni</th>
	</tr>
	$tableBody
</table>
<p><a href="$gvPath/application/adminOperatorEdit">+ Aggiungi operatore</a></p>
<p><br/><a href="$gvPath/application/adminPage">Torna al menù principale</a></p>
EOS;
        return $ret;
    }
    
    public function getPageHeader() {
        $ret = '<h1>Gestione operatori</h1>';
        $ret .= Page::getDelayedMsgBlock();
        return $ret;
    }

}
