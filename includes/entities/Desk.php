<?php

/**
 * Description of Operator
 *
 * @author sergio
 */
class Desk extends DatabaseTableWithId {
    
    /**
     * @var array
     */
    protected $columns = array(
        'desk_number',
        'desk_ip_address',
        'desk_op_code',
        'desk_last_activity_time',
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'desk_id';
    
    /**
     * @var string
     */
    protected $tableName = 'desk';
    
    protected $desk_id;
    protected $desk_number;
    protected $desk_ip_address;
    protected $desk_op_code = null;
    protected $desk_last_activity_time = null;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'Desk' );
    }
    
    public static function fromDatabaseById( $desk_id ) {
        return parent::fromDatabaseByParameterGeneric( 'desk_id', $desk_id, 'Desk' );
    }
    
    public static function fromDatabaseByNumber( $desk_number ) {
        return parent::fromDatabaseByParameterGeneric( 'desk_number', $desk_number, 'Desk' );
    }
    
    public static function fromDatabaseByIpAddress( $desk_ip_address ) {
        return parent::fromDatabaseByParameterGeneric( 'desk_ip_address', $desk_ip_address, 'Desk' );
    }
    
    public static function fromDatabaseByOperator( $desk_op_code ) {
        return parent::fromDatabaseByParameterGeneric( 'desk_op_code', $desk_op_code, 'Desk' );
    }
    
    public static function fromDatabaseCompleteList() {
        $sql = "SELECT * FROM desk ORDER BY desk_number";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( "fromDatabaseCompleteList: Error while reading desk from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }

    public static function getUsedDeskNumbers() {
        $sql = "SELECT desk_number FROM desk ORDER BY desk_number";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( "getUsedDeskNumbers: Error while reading data" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = $row['desk_number'];
        }
        return $output;
    }

    /**
     * @return Desk
     */
    public static function newRecord() {
        $ret = new Desk();
        $ret->desk_last_activity_time = null;
        $ret->desk_op_code = null;
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->desk_id;
    }
    
    public function getNumber() {
        return $this->desk_number;
    }

    public function getIpAddress() {
        return $this->desk_ip_address;
    }

    public function getOpCode() {
        return $this->desk_op_code;
    }

    public function getLastActivityTime() {
        return $this->desk_last_activity_time;
    }

    public function setNumber( $desk_number ) {
        if ( $desk_number != $this->desk_number ) {
            $this->saved = false;
            $this->desk_number = $desk_number;
        }
    }

    public function setIpAddress( $desk_ip_address ) {
        if ( $desk_ip_address != $this->desk_ip_address ) {
            $this->saved = false;
            $this->desk_ip_address = $desk_ip_address;
        }
    }

    public function setOpCode( $desk_op_code ) {
        if ( $desk_op_code != $this->desk_op_code ) {
            $this->saved = false;
            $this->desk_op_code = $desk_op_code;
        }
    }

    public function setLastActivityTime( $desk_last_activity_time ) {
        if ( $desk_last_activity_time != $this->desk_last_activity_time ) {
            $this->saved = false;
            $this->desk_last_activity_time = $desk_last_activity_time;
        }
    }

    public function updateLastActivityTime() {
        $this->saved = false;
        $this->desk_last_activity_time = time();
    }

    public function isOpen() {
        global $gvSessionTimeout;
        
        if ( !$this->desk_last_activity_time || !$this->desk_op_code ) {
            // Closed
            return false;
        }
        $last = $this->desk_last_activity_time;
        if ( time() - $last >= $gvSessionTimeout ) {
            // Session timed out, update tables
            Operator::clearTableForLogout( $this->desk_op_code, $this, null );
            return false;
        }
        return true;
    }
}