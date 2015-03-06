<?php

/**
 * Description of DisplayMain
 *
 * @author sergio
 */
class DisplayMain extends DatabaseTableWithId implements JsonSerializable {

    /**
     * @var array
     */
    protected $columns = array(
        'dm_ticket',
        'dm_desk'
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'dm_id';
    
    /**
     * @var string
     */
    protected $tableName = 'display_main';
    
    protected $dm_id;
    protected $dm_ticket = 'A000';
    protected $dm_desk = 0;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'DisplayMain' );
    }
    
    public static function fromDatabaseById( $dm_id ) {
        return parent::fromDatabaseByParameterGeneric( 'dm_id', $dm_id, 'DisplayMain' );
    }
    
    public static function fromDatabaseCompleteList( $td_code = '' ) {
        if ( !empty( $td_code ) ) {
            $td_code = substr( $td_code, 0, 1 );
            $td_code = strtoupper( $td_code );
            $td_code = $td_code . '%';
            $where = 'WHERE dm_ticket LIKE ? ';
            $params = array( $td_code );
        } else {
            $where = '';
            $params = array();
        }

        $sql = "SELECT * FROM display_main {$where}ORDER BY dm_id DESC";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( $params ) ) {
            throw new Exception( __METHOD__
                    . " Error while reading display_main from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ( $rows as $row ) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }
    
    /**
     * @return DisplayMain
     */
    public static function newRecord() {
        $ret = new DisplayMain();
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->dm_id;
    }

    public function getTicket() {
        return $this->dm_ticket;
    }

    public function getDesk() {
        return $this->dm_desk;
    }

    public function setTicket( $ticket ) {
        if ( $ticket instanceof Ticket ) {
            $ticket = $ticket->getTextString();
        }
        if ( $this->dm_ticket != $ticket ) {
            $this->saved = false;
            $this->dm_ticket = $ticket;
        }
    }

    public function setDesk( $desk ) {
        if ( $desk instanceof Desk ) {
            $desk = $desk->getNumber();
        }
        if ( $this->dm_desk != $desk ) {
            $this->saved = false;
            $this->dm_desk = $desk;
        }
    }

    public static function addRecord( $ticket ) {
        $entry = DisplayMain::newRecord();
        list( $min, $max ) = self::getMinMaxId();
        $entry->dm_id = $max + 1;
        $entry->setTicket( $ticket );
        $desk = $ticket->getDeskNumber();
        $entry->setDesk( $desk );
        $entry->save();

        // Delete an old record?
        if ( $max - $min == 99 ) {
            $old = self::fromDatabaseById( $min );
            $old->delete();
        }
    }

    // return array( min, max )
    protected static function getMinMaxId() {
        $sql = "SELECT MAX( dm_id ) AS maxx, MIN( dm_id ) AS minn FROM display_main";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( "Error while computing max or min index." );
        }
        $result = $stmt->fetch( PDO::FETCH_ASSOC );
        return array( $result['minn'], $result['maxx'] );
    }

    public function jsonSerialize() {
        return array(
            'ticket' => $this->dm_ticket,
            'desk' => $this->dm_desk
        );
    }

}
