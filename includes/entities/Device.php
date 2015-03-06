<?php

/**
 * Description of Device
 *
 * @author sergio
 */
class Device extends DatabaseTableWithId {
    
    /**
     * @var array
     */
    protected $columns = array(
        'dev_ip_address',
        'dev_desk_number',
        'dev_td_code'
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'dev_id';
    
    /**
     * @var string
     */
    protected $tableName = 'device';
    
    protected $dev_id;
    protected $dev_ip_address;
    protected $dev_desk_number;
    protected $dev_td_code = null;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'Device' );
    }
    
    public static function fromDatabaseById( $dev_id ) {
        return parent::fromDatabaseByParameterGeneric( 'dev_id', $dev_id, 'Device' );
    }
    
    public static function fromDatabaseByIpAddress( $dev_ip_address ) {
        return parent::fromDatabaseByParameterGeneric( 'dev_ip_address', $dev_ip_address, 'Device' );
    }
    
    public static function fromDatabaseCompleteList() {
        $sql = "SELECT * FROM device ORDER BY dev_ip_address";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( "fromDatabaseCompleteList: Error while reading device from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }

    /**
     * @return Desk
     */
    public static function newRecord() {
        $ret = new Device();
        $ret->dev_desk_number = 0;
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->dev_id;
    }
    

    public function getIpAddress() {
        return $this->dev_ip_address;
    }
    
    public function getDeskNumber() {
        return $this->dev_desk_number;
    }

    public function getTdCode() {
        return $this->dev_td_code;
    }

    public function setDeskNumber( $dev_desk_number ) {
        if ( $dev_desk_number != $this->dev_desk_number ) {
            $this->saved = false;
            $this->dev_desk_number = $dev_desk_number;
        }
    }

    public function setIpAddress( $dev_ip_address ) {
        if ( $dev_ip_address != $this->dev_ip_address ) {
            $this->saved = false;
            $this->dev_ip_address = $dev_ip_address;
        }
    }

    public function setTdCode( $dev_td_code ) {
        $dev_td_code = strtoupper( $dev_td_code );
        if ( $dev_td_code != $this->dev_td_code ) {
            $this->saved = false;
            $this->dev_td_code = $dev_td_code;
        }
    }

}
