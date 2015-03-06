<?php

/**
 * Description of GetTopicalDomains
 *
 * @author sergio
 */
class GetTopicalDomains extends Page {
    
    public function __construct() {
        Database::lockTables( false );
    }

    public function canUse( $userLevel ) {
        return true;
    }

    public function execute() {
        // Cannot be posted
        return true;
    }

    public function getOutput() {
        $tds = TopicalDomain::fromDatabaseCompleteList( true );
        $content = array();
        $i = 0;
        foreach ( $tds as $td ) {
            $content[$i]['code'] = $td->getCode();
            $content[$i]['name'] = $td->getName();
            $content[$i]['description'] = $td->getDescription();
            $content[$i]['color'] = $td->getColor();
            $i++;
        }
        $jsonOutput = new JsonOutput();
        $jsonOutput->setContent( $content );
        
        return $jsonOutput;
    }

}
