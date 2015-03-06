<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JsonOutput
 *
 * @author sergio
 */
class JsonOutput extends Output {
    
    /**
     * @var array 
     */
    public $jsonContent;
    
    public function __construct( $willCloseConnection = false, $disableCache = true ) {
        parent::__construct( $willCloseConnection, $disableCache );
        $this->jsonContent = array();
        $this->addHttpHeader( "Content-Type", "application/json" );
    }
    
    public function outputHttpBody() {
        echo json_encode( $this->jsonContent );
    }
    
    public function setContent( $array ) {
        $this->jsonContent = $array;
    }

}
