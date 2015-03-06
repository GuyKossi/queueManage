<?php

/**
 * Description of newPHPClass
 *
 * @author Antonio
 */
abstract class Output {
    
    /**
     * HeaderName => HeaderValue
     * @var array
     */
    public $httpHeaders;
    
    /**
     * Defaults to 200
     * @var integer
     */
    public $httpStatusCode;
    
    /**
     * If left empty, message will be determined by StatusCode
     * @var string
     */
    public $httpStatusMessage;
    
    /**
     * @var boolean
     */
    public $willCloseConnection;
    
    /**
     * @var boolean 
     */
    public $disableCache;
    
    // TODO: to complete
    public static $HTTP_STATUS_MESSAGES = [
        200 => "OK",
        301 => "Moved permanently",
        302 => "Found",
        303 => "See other",
        304 => "Not modified",
        400 => "Bad request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not found",
        500 => "Internal server error",
        501 => "Not implemented",
    ];
    
    public static function getDefaultHttpStatusMessage( $statusCode ) {
        if ( array_key_exists( $statusCode, self::$HTTP_STATUS_MESSAGES ) ) {
            return self::$HTTP_STATUS_MESSAGES[$statusCode];
        } else {
            return "Message unkwown";
        }
    }
    
    public function __construct( $willCloseConnection = false, $disableCache = true ) {
        $this->httpHeaders = array();
        $this->httpStatusMessage = '';
        $this->httpStatusCode = 200;
        $this->willCloseConnection = $willCloseConnection;
        $this->disableCache = $disableCache;
        ob_start();
    }
    
    public abstract function outputHttpBody();
    
    public function outputHttpHeaders() {
        if ( $this->httpStatusMessage === '' ) {
            $this->httpStatusMessage = self::getDefaultHttpStatusMessage( $this->httpStatusCode );
        }
        header('HTTP/1.1 ' . $this->httpStatusCode . ' ' .
                $this->httpStatusMessage );
        
        if ( $this->disableCache ) {
            header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
            header('Pragma: no-cache'); // HTTP 1.0.
            header('Expires: 0'); // Proxies.
        }
        
        foreach ( $this->httpHeaders as $key => $value ) {
            header( "$key: $value" );
        }
    }
    
    public function addHttpHeader( $name, $value ) {
        if ( strtolower( $name ) === "connection" ) {
            // Connection header is handled differently
            $this->willCloseConnection = ( strtolower( $value ) === "close" );
            return;
        }
        $this->httpHeaders[$name] = $value;
    }
    
    public function setHttpStatusCode( $statusCode ) {
        $this->httpStatusCode = (int) $statusCode;
    }
    
    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }
    
    public function setHttpStatusMessage( $statusMsg ) {
        $this->httpStatusMessage = $statusMsg;
    }
    
    public function getHttpStatusMessage() {
        return $this->httpStatusMessage;
    }
    
    public function closeConnection( $value ) {
        $this->willCloseConnection = (bool) $value;
    }
    
    public function disableCache( $value ) {
        $this->disableCache = (bool) $value;
    }


    public function output() {

        $jobs = JobQueue::getInstance();
        if ( $jobs->countJobs() > 0 && !$this->willCloseConnection ) {
            gfDebug( __METHOD__ . " Forced close connection for background jobs." );
            $this->closeConnection( true );
        }

        $this->outputHttpHeaders();
        $this->outputHttpBody();
        
        if ( $this->willCloseConnection ) {
            @ini_set( 'zlib.output_compression', 'Off' );
            @apache_setenv( 'no-gzip', 1 );
            header( "Connection: close");
            $outputSize = ob_get_length();
            header( "Content-Length: $outputSize" );
        }
        
        ob_end_flush();
        ob_flush();
        flush();

        if ( $this->willCloseConnection && session_id() ) {
            session_write_close();
        }
    }
}
