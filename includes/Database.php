<?php

/**
 * Description of Database
 *
 * @author sergio
 */
class Database {

    public static $tables = array(
        'ban',
        'desk',
        'device',
        'display_main',
        'office',
        'operator',
        'ticket_exec',
        'ticket_in',
        'ticket_stats',
        'topical_domain',
    );

    /**
     * @var PDO 
     */
    private static $dbConnection = null;
    private static $lockTables = true;
    
    private function __construct() {
        // Private constructor
    }
    
    /**
     * 
     * @global array $gvDbConfig
     * @return PDO
     */
    public static function getConnection() {
        if ( self::$dbConnection == null ) {
            global $gvDbConfig;
            $dsn = "mysql:host={$gvDbConfig['host']};dbname={$gvDbConfig['database']}";
            self::$dbConnection = new PDO(
                    $dsn,
                    $gvDbConfig['username'],
                    $gvDbConfig['password']
            );
            
            if ( self::$lockTables ) {
                self::$dbConnection->exec(
                "SET autocommit=0"
                );
                self::$dbConnection->exec(
                    "LOCK TABLES " .
                    implode( ' WRITE, ', self::$tables ) .
                    ' WRITE'
                );
                gfDebug( 'Tables are now locked' );
            }
        }
        return self::$dbConnection;
    }
    
    public static function prepareStatement( $sql ) {
        $conn = self::getConnection();
        return $conn->prepare( $sql );
    }

    public static function hasBeenUsed() {
        return self::$dbConnection !== null;
    }

    public static function commit() {
        if ( !self::$dbConnection ) {
            throw new Exception( __METHOD__ . " Unable to commit, unused database." );
        }
        if ( self::$lockTables ) {
            self::$dbConnection->exec( "COMMIT" );
            self::$dbConnection->exec( "UNLOCK TABLES" );
        }
    }

    public static function lockTables( $value ) {
        if ( self::$dbConnection ) {
            throw new Exception( "Unable to lock tables, connection already established." );
        }
        self::$lockTables = (bool) $value;
        gfDebug( 'Tables won\'t be locked' );
    }

}
