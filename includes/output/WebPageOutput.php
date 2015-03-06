<?php

class WebPageOutput extends Output {
    
    /**
     * @var string
     */
    public $htmlPageTitle;
    
    /**
     * @var array
     */
    public $htmlHeaders;
    
    /**
     * @var string 
     */
    public $htmlBodyHeader;
    /**
     * @var string 
     */
    public $htmlBodyContent;
    /**
     * @var string 
     */
    public $htmlBodyFooter;
    
    public function __construct( $willCloseConnection = false, $disableCache = true ) {
        parent::__construct( $willCloseConnection, $disableCache );
        $this->htmlPageTitle = "No title";
        $this->htmlHeaders = array();
        $this->htmlBodyHeader = "";
        $this->htmlBodyContent = "";
        $this->htmlBodyFooter = "";
        $this->addHttpHeader( "Content-Type", "text/html; charset=utf-8" );
    }


    public function outputHttpBody() {
        global $gvLangCode;
        
        $output = "<!doctype html>\n";
        $output .= "<html lang=\"$gvLangCode\">\n";
        $output .= "<head>\n";
        $output .= "<title>$this->htmlPageTitle</title>\n";
        foreach ($this->htmlHeaders as $header) {
            $output .= $header . PHP_EOL;
        }
        $output .= "</head>\n";
        $output .= "<body>\n";
        $output .= "<!-- Start Header -->\n";
        $output .= $this->htmlBodyHeader . PHP_EOL;
        $output .= "<!-- End Header -->\n";
        $output .= "<!-- Start Content -->\n";
        $output .= $this->htmlBodyContent . PHP_EOL;
        $output .= "<!-- End Content -->\n";
        $output .= "<!-- Start Footer -->\n";
        $output .= $this->htmlBodyFooter . PHP_EOL;
        $output .= "<!-- End Footer -->\n";
        $output .= "</body>\n";
        $output .= "</html>\n";
        
        echo $output;
    }
    
    public function linkStyleSheet( $styleSheetPath ) {
        $styleSheetPath = htmlspecialchars( $styleSheetPath );
        $linkTag = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$styleSheetPath\">";
        $this->htmlHeaders[] = $linkTag;
    }
    
    public function addJavascript( $scriptPath ) {
        $scriptPath = htmlspecialchars( $scriptPath );
        $scriptTag = "<script src=\"$scriptPath\" type=\"text/javascript\"></script>";
        $this->htmlHeaders[] = $scriptTag;
    }
    
    public function importJquery() {
        global $gvJqueryUrl;
        $this->addJavascript( $gvJqueryUrl );
    }

    public function getHtmlPageTitle() {
        return $this->htmlPageTitle;
    }

    public function setHtmlPageTitle( $htmlPageTitle ) {
        $htmlPageTitle = htmlspecialchars( $htmlPageTitle );
        $this->htmlPageTitle = $htmlPageTitle;
    }

    public function addHtmlHeader( $htmlTag ) {
        if( preg_match( '/< *title/i', $htmlTag ) ) {
            // Do not consider <title> tags. See $htmlPageTitle
            return;
        }
        $this->htmlHeaders[] = $htmlTag;
    }

    public function getHtmlBodyHeader() {
        return $this->htmlBodyHeader;
    }

    public function getHtmlBodyContent() {
        return $this->htmlBodyContent;
    }

    public function getHtmlBodyFooter() {
        return $this->htmlBodyFooter;
    }

    public function setHtmlBodyHeader( $htmlBodyHeader ) {
        $this->htmlBodyHeader = $htmlBodyHeader;
    }

    public function setHtmlBodyContent( $htmlBodyContent ) {
        $this->htmlBodyContent = $htmlBodyContent;
    }

    public function setHtmlBodyFooter( $htmlBodyFooter ) {
        $this->htmlBodyFooter = $htmlBodyFooter;
    }

    public function loadJqueryUi() {
        $this->linkStyleSheet( "//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css" );
        $this->addJavascript( "//code.jquery.com/jquery-1.10.2.js" );
        $this->addJavascript( "//code.jquery.com/ui/1.11.2/jquery-ui.js" );
    }

}
