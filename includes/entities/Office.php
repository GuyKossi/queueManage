<?php

/**
 * Description of Office
 *
 * @author sergio
 */
class Office extends DatabaseTableWithId {

    private static $replaceFrom =  array( 'à', 'á', 'è', 'é', 'ì', 'í', 'ò', 'ó', 'ù', 'ú', "'", '"', '`', '%', '_' );
    private static $replaceTo =    array( 'a', 'a', 'e', 'e', 'i', 'i', 'o', 'o', 'u', 'u', ' ', ' ', ' ', ' ', ' ' );
    /**
     * @var array
     */
    protected $columns = array(
        'of_code',
        'of_name',
        'of_city',
        'of_search',
        'of_address',
        'of_latitude',
        'of_longitude',
        'of_host',
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'of_id';
    
    /**
     * @var string
     */
    protected $tableName = 'office';
    
    protected $of_id;
    protected $of_code;
    protected $of_name;
    protected $of_city;
    protected $of_search;
    protected $of_address;
    protected $of_latitude;
    protected $of_longitude;
    protected $of_host;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'Office' );
    }
    
    public static function fromDatabaseById( $of_id ) {
        return parent::fromDatabaseByParameterGeneric( 'of_id', $of_id, 'Office' );
    }
    
    public static function fromDatabaseByCode( $of_code ) {
        return parent::fromDatabaseByParameterGeneric( 'of_code', $of_code, 'Office' );
    }
    
    public static function fromDatabaseCompleteList() {
        $sql = "SELECT * FROM office ORDER BY of_code";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( __METHOD__
                    . " Error while reading offices from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }

    public static function fromDatabaseSearchByCity( $city ) {
        $city = mb_strtolower( trim( $city ), 'utf-8' );
        $city = str_replace( self::$replaceFrom , self::$replaceTo,  $city );
        $words = explode( ' ' , $city );
        // Remove empty words and prepare for regex
        foreach ( $words as $i => $word ) {
            if ( $word !== '' ) {
                $words[$i] = preg_quote( $word );
            } else {
                unset( $words[$i] );
            }
        }

        $sql = "SELECT * FROM office WHERE of_search REGEXP ? " .
            "ORDER BY of_city, of_address";
        $param = "(^| )(" . implode( "|", $words ) . ")($| )";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( array( $param ) ) ) {
            gfDebug( print_r( $stmt->errorInfo(), true ) );
            gfDebug( print_r( $words, true ) );
            throw new Exception( __METHOD__
                    . " Error while reading offices from database" );
        }
        
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        $offices = array();
        foreach ( $rows as $row ) {
            $offices[] = self::newFromDatabaseRow( $row );
        }

        // Sort results to show most relevant entries first
        $ranking = array();
        for ( $i = 1; $i <= count( $words ); $i++ ) { 
            $ranking[$i] = array();
        }
        foreach ( $offices as $office ) {
            $search = $office->getSearch();
            $level = 0;
            foreach ( $words as $word ) {
                gfDebug( "Analizzo parola $word per $search" );
                if ( strpos( $search,  $word ) !== false ) {
                    gfDebug( "La contiene e alzo il livello" );
                    $level++;
                }
            }
            $ranking[$level][] = $office;
            gfDebug( "La città {$office->getCity()} ha livello finale $level" );
        }
        gfDebug( print_r( $words, true ) );
        // Construct final array
        $ret = array();
        foreach ( $ranking as $level ) {
            $ret = array_merge( $level, $ret );
        }

        return $ret;
    }

    // This will return array entities instead of objects
    // because of distance attributes
    public static function fromDatabaseSearchByCoords( $lat, $lon ) {
        $db = Database::getConnection();
        $lat = $db->quote( $lat );
        $lon = $db->quote( $lon );
        $sql = "SELECT of_code, of_name, of_city, of_address, of_host, " .
            "6371 * 2 * ASIN( SQRT( POWER( SIN( ( $lat - of_latitude ) * " .
            "PI() / 180 / 2 ), 2 ) + COS( $lat * PI() / 180 ) * " .
            "COS( of_latitude * PI() / 180 ) * POWER( SIN( ( $lon - of_longitude ) * " .
            "PI() / 180 / 2 ), 2 ) ) ) AS of_distance " .
            "FROM office HAVING of_distance < 20 ORDER BY of_distance";
        $stmt = $db->prepare( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( __METHOD__
                    . " Error while reading offices from database" );
        }
        return $stmt->fetchall( PDO::FETCH_ASSOC );
    }

    /**
     * @return Office
     */
    public static function newRecord() {
        $ret = new Office();
        $ret->of_address = '';
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->of_id;
    }

    public function getCode() {
        return $this->of_code;
    }

    public function getName() {
        return $this->of_name;
    }

    public function getCity() {
        return $this->of_city;
    }

    public function getSearch() {
        return $this->of_search;
    }

    public function getAddress() {
        return $this->of_address;
    }

    public function getCoords() {
        return array( $this->of_latitude, $this->of_longitude );
    }

    public function getLatitude() {
         return $this->of_latitude;
    }

    public function getLongitude() {
        return $this->of_longitude;
    }

    public function getHost() {
        return $this->of_host;
    }

    public function setCode( $of_code ) {
        if ( $this->of_code != $of_code ) {
            $this->saved = false;
            $this->of_code = $of_code;
        }
    }

    public function setName( $of_name ) {
        if ( $this->of_name != $of_name ) {
            $this->saved = false;
            $this->of_name = $of_name;
        }
    }

    public function setCity( $of_city ) {
        if ( $this->of_city != $of_city ) {
            $this->saved = false;
            $this->of_city = $of_city;
        }
    }

    public function setSearch( $of_search ) {
        if ( $this->of_search != $of_search ) {
            $this->saved = false;
            $this->of_search = $of_search;
        }
    }

    public function setAddress( $of_address ) {
        if ( $this->of_address != $of_address ) {
            $this->saved = false;
            $this->of_address = $of_address;
        }
    }

    public function setLatitude( $of_latitude ) {
        if ( $this->of_latitude != $of_latitude ) {
            $this->saved = false;
            $this->of_latitude = $of_latitude;
        }
    }

    public function setLongitude( $of_longitude ) {
        if ( $this->of_longitude != $of_longitude ) {
            $this->saved = false;
            $this->of_longitude = $of_longitude;
        }
    }

    public function setHost( $of_host ) {
        if ( $this->of_host != $of_host ) {
            $this->saved = false;
            $this->of_host = $of_host;
        }
    }

}
