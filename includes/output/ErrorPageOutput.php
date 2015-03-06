<?php

class ErrorPageOutput extends WebPageOutput {

    public function __construct( $errorTitle, $errorMsg, $errorFooter = '' ) {
        parent::__construct( false, true );
        $this->setHtmlPageTitle( $errorTitle );
        $this->setHtmlBodyHeader( "<h1>$errorTitle</h1>" );
        $this->setHtmlBodyContent( "<p>$errorMsg</p>" );
        $this->setHtmlBodyFooter( $errorFooter );
    }
}
