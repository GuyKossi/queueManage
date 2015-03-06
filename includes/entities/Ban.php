<?php

/**
 * Description of Ban
 *
 * @author sergio
 */
class Ban extends DatabaseTableWithId {

    public static $BAN_DURATIONS = array(
        3 =>  604800, # 7 days
        4 => 1296000, # 15 days
        5 => 2592000, # 30 days
    );

    public static $MAX_BAN = array(
        'value' => 5,
        'duration' => 2592000, # 30 days
    );
    
    /**
     * @var array
     */
    protected $columns = array(
        'ban_source',
        'ban_trash_count',
        'ban_time_end',
        'ban_source_id'
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'ban_id';
    
    /**
     * @var string
     */
    protected $tableName = 'ban';
    
    protected $ban_id;
    protected $ban_source;
    protected $ban_trash_count;
    protected $ban_time_end;
    protected $ban_source_id;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'Ban' );
    }
    
    public static function fromDatabaseById( $ban_id ) {
        return parent::fromDatabaseByParameterGeneric( 'ban_id', $ban_id, 'Ban' );
    }
    
    public static function fromDatabaseBySourceId( $ban_source_id ) {
        return parent::fromDatabaseByParameterGeneric( 'ban_source_id', $ban_source_id, 'Ban' );
    }
    
    public static function fromDatabaseCompleteList() {
        $sql = "SELECT * FROM ban ORDER BY ban_id";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( __METHOD__
                    . " Error while reading ban from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }
    
    /**
     * @return Ban
     */
    public static function newRecord() {
        $ret = new Ban();
        $ret->ban_trash_count = 0;
        $ret->ban_time_end = 0;
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->ban_id;
    }

    public function getSource() {
        return $this->ban_source;
    }

    public function getSourceId() {
        return $this->ban_source_id;
    }

    public function getTrashCount() {
        return $this->ban_trash_count;
    }

    public function getTimeEnd() {
        return $this->ban_time_end;
    }

    public function setSource( $ban_source ) {
        if ( !in_array( $ban_source, array( 'web', 'app' ) ) ) {
            throw new Exception( "Invalid source for ban table." );
        }
        if ( $this->ban_source != $ban_source ) {
            $this->saved = false;
            $this->ban_source = $ban_source;
        }
    }

    public function setTrashCount( $ban_trash_count ) {
        $ban_trash_count = (int) $ban_trash_count;
        if ( $ban_trash_count < 0 ) {
            $ban_trash_count = 0;
        }
        if ( $ban_trash_count != $this->ban_trash_count ) {
            $this->saved = false;
            $this->ban_trash_count = $ban_trash_count;
        }
    }

    public function setTimeEnd( $ban_time_end ) {
        $ban_time_end = (int) $ban_time_end;
        if ( $ban_time_end != $this->ban_time_end ) {
            $this->saved = false;
            $this->ban_time_end = $ban_time_end;
        }
    }

    public function setSourceId( $ban_source_id ) {
        if ( $this->ban_source_id != $ban_source_id ) {
            $this->saved = false;
            $this->ban_source_id = $ban_source_id;
        }
    }

    public function incrementTrashCount() {
        $newValue = $this->getTrashCount() + 1;
        $this->setTrashCount( $newValue );
        // Ban the user if necessary
        if ( array_key_exists( $this->ban_trash_count, self::$BAN_DURATIONS ) ) {
            $timestamp = time() + self::$BAN_DURATIONS[$this->ban_trash_count];
            $this->setTimeEnd( $timestamp );
        } elseif ( $this->ban_trash_count > self::$MAX_BAN['value'] ) {
            $timestamp = time() + self::$MAX_BAN['duration'];
            $this->setTimeEnd( $timestamp );
        }
    }

    public function decrementTrashCount() {
        if ( $this->getTrashCount() > 0 ) {
            $newValue = $this->getTrashCount() - 1;
            $this->setTrashCount( $newValue );
        }
    }

    public static function isBanned( $ban_source_id ) {
        $sql = "SELECT ban_id FROM ban " .
            "WHERE ban_source_id=? AND ban_time_end > UNIX_TIMESTAMP() LIMIT 1";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( array( $ban_source_id ) ) ) {
            throw new Exception( __METHOD__
                    . " Error while reading ban from database" );
        }
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        if ( $row ) {
            return true;
        } else {
            return false;
        }
    }

    public static function sourceExists( $ban_source, $ban_source_id ) {
        $sql = "SELECT ban_id FROM ban " .
            "WHERE ban_source = ? AND ban_source_id = ? LIMIT 1";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( array( $ban_source, $ban_source_id ) ) ) {
            throw new Exception( __METHOD__
                    . " Error while reading ban from database" );
        }
        $row = $stmt->fetch( PDO::FETCH_ASSOC );
        if ( $row ) {
            return true;
        } else {
            return false;
        }
    }

    public static function recordTicketTrash( $ticket ) {
        $source = $ticket->getSource();
        $sourceId = $ticket->getSourceId();
        // check if the record is present in the table
        $ban = self::fromDatabaseBySourceId( $sourceId );
        if ( $ban ) {
            $ban->incrementTrashCount();
            gfDebug( 'Incrementato valore trash, ora risulta ' . $ban->getTrashCount() );
        } else {
            $ban = self::newRecord();
            $ban->setSource( $source );
            $ban->setSourceId( $sourceId );
            $ban->setTrashCount( 1 );
        }
        $ban->save();
    }

}
