<?php

/**
 * Description of GsSearch
 *
 * @author sergio
 */
class GsSearch extends Page {
    private $city;
	
    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        global $gvPath;

        if ( !isset( $_GET['q'] ) || !trim( $_GET['q'] ) ) {
            // Redirect to home page
            return new RedirectOutput( "$gvPath/globalServer/homePage" );
        }

        $this->city = strtolower( $_GET['q'] );

        $output = new WebPageOutput();
        $output->linkStyleSheet( "$gvPath/assets/css/style.css");
        $output->setHtmlPageTitle( $this->getTitle() );
        $output->setHtmlBodyHeader( $this->getHeader() );
        $output->setHtmlBodyContent( $this->getContent() );
        return $output;
    }

    public function getContent() {
        global $gvPath;
        $tableRows = $this->getTableRows();
        $ret = <<<EOS
<table id="listTable">
    <tr>
        <th>Codice</th>
        <th>Nome</th>
        <th>Città</th>
        <th>Indirizzo</th>
        <th>Prenotazione online</th>
    </tr>
    $tableRows
</table>
<p><a href="$gvPath/globalServer/homePage">Esegui una nuova ricerca</a></p>
EOS;
        return $ret;
    }

    public function getTableRows() {
        $offices = Office::fromDatabaseSearchByCity( $this->city );
        if ( !$offices ) {
            return "\n    <tr><td colspan=\"4\">Nessun ufficio trovato.</td></tr>";
        }
        $rows = '';
        foreach ( $offices as $office ) {
            $rows .= <<<EOS
    <tr>
        <td>{$office->getCode()}</td>
        <td>{$office->getName()}</td>
        <td>{$office->getCity()}</td>
        <td>{$office->getAddress()}</td>
        <td><a href="http://{$office->getHost()}">{$office->getHost()}</a></td>
    </tr>
EOS;
        }
        return $rows;
    }

    public function getHeader() {
        return "<h1>{$this->getTitle()}</h1>";
    }

    public function getTitle() {
        return 'Ricerca uffici';
    }

}
