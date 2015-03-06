<?php

/**
 * Description of RedirectOutput
 *
 * @author sergio
 */
class RedirectOutput extends Output {
    
    public function __construct( $location, $isTemporary = true ) {
        parent::__construct();
        $this->setHttpStatusCode( $isTemporary ? '302' : '301' );
        $this->addHttpHeader( 'Location', $location );
    }


    public function outputHttpBody() {
        // No body for redirects
        echo '';
    }

}
