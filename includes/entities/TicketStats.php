<?php

/**
 * Description of TicketStats
 *
 * @author sergio
 */
class TicketStats extends DatabaseTableWithId {
    
    /**
     * @var array
     */
    protected $columns = array(
        'ts_code',
        'ts_time_in',
        'ts_time_exec',
        'ts_time_out',
        'ts_source',
        'ts_op_code',
        'ts_is_trash',
        'ts_desk_number',
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'ts_id';
    
    /**
     * @var string
     */
    protected $tableName = 'ticket_stats';
    
    protected $ts_id;
    protected $ts_code;
    protected $ts_time_in;
    protected $ts_time_exec = null;
    protected $ts_time_out;
    protected $ts_source;
    protected $ts_op_code = null;
    protected $ts_is_trash = false;
    protected $ts_desk_number = null;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'TicketStats' );
    }

    public static function fromDatabaseCompleteList() {
        return self::fromDatabaseList();
    }

    private static function fromDatabaseList( $sqlWhere = '', $parameters = array() ) {
        $sql = "SELECT * FROM ticket_stats $sqlWhere ORDER BY ts_time_out";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( $parameters ) ) {
            throw new Exception( __METHOD__
                    . " Error while reading ticketStats from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }

    public static function fromDatabaseListByCode( $td_code, $time_from, $time_to ) {
        $where = "WHERE ts_code = ? AND ts_time_exec BETWEEN ? AND ?";
        $param = array( $td_code, $time_from, $time_to );
        return self::fromDatabaseList( $where, $param );
    }

    public static function fromDatabaseListByOperator( $op_code, $time_from, $time_to ) {
        $where = "WHERE ts_op_code = ? AND ts_time_exec BETWEEN ? AND ?";
        $param = array( $op_code, $time_from, $time_to );
        return self::fromDatabaseList( $where, $param );
    }

    public static function fromDatabaseListBySource( $source, $time_from, $time_to ) {
        $where = "WHERE ts_source = ? AND ts_time_exec BETWEEN ? AND ?";
        $param = array( $source, $time_from, $time_to );
        return self::fromDatabaseList( $where, $param );
    }

    /**
     * @return TicketStats
     */
    public static function newRecord() {
        $ret = new TicketStats();
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }

    public static function newFromTicket( $ticket, $ts_time_out = null ) {
        if ( !$ts_time_out ) {
            $ts_time_out = time();
        }

        $ret = self::newRecord();
        $ret->setCode( $ticket->getCode() );
        $ret->setTimeIn( $ticket->getTimeIn() );
        $ret->setSource( $ticket->getSource() );
        
        if ( $ticket->getTable() == 'ticket_in' ) {
            $ret->setTimeExec( null );
            $ret->setIsTrash( false );
            $ret->setTimeOut( $ts_time_out );
        } else {
            global $gvTrashThreshold, $gvSessionTimeout;

            $ret->setOpCode( $ticket->getOpCode() );
            $ret->setDeskNumber( $ticket->getDeskNumber() );
            $ret->setTimeExec( $ticket->getTimeExec() );

            if ( $ts_time_out - $ticket->getTimeExec() > $gvSessionTimeout ) {
                // Never set exec duration beyond sessionTimeout
                $ts_time_out = $ticket->getTimeExec() + $gvSessionTimeout;
            }
            $ret->setTimeOut( $ts_time_out );

            if (
                in_array( $ret->ts_source, array( 'app', 'web' ) )
                && $ret->ts_time_out - $ret->ts_time_exec < $gvTrashThreshold
            ) {
                // This ticket is trash
                $ret->setIsTrash( true );
                Ban::recordTicketTrash( $ticket );

                // Send trash notice (only for app)
                if ( $ticket->getSource() == 'app' ) {
                    $call = function () use ( $ticket ) {
                        $sender = new AppPushSender( $ticket->getSourceId() );
                        $sender->sendTrashNotice();
                    };
                    $jobs = JobQueue::getInstance();
                    $jobs->addJob( $call );
                }
            }
        }
        return $ret;

    }
    
    // Getter
    function getId() {
        return $this->ts_id;
    }

    function getCode() {
        return $this->ts_code;
    }

    function getTimeIn() {
        return $this->ts_time_in;
    }

    function getTimeExec() {
        return $this->ts_time_exec;
    }

    function getTimeOut() {
        return $this->ts_time_out;
    }

    function getSource() {
        return $this->ts_source;
    }

    function getOpCode() {
        return $this->ts_op_code;
    }

    function getIsTrash() {
        return $this->ts_is_trash;
    }

    function getDeskNumber() {
        return $this->ts_desk_number;
    }

    // Setters

    function setCode( $ts_code ) {
        $ts_code = strtoupper( $ts_code );
        if ( $this->ts_code != $ts_code ) {
            $this->ts_code = $ts_code;
            $this->saved = false;
        }
    }

    function setTimeIn( $ts_time_in ) {
        $ts_time_in = (int) $ts_time_in;
        if ( $this->ts_time_in != $ts_time_in ) {
            $this->ts_time_in = $ts_time_in;
            $this->saved = false;
        }
    }

    function setTimeExec( $ts_time_exec ) {
        $ts_time_exec = (int) $ts_time_exec;
        if ( $this->ts_time_exec != $ts_time_exec ) {
            $this->ts_time_exec = $ts_time_exec;
            $this->saved = false;
        }
    }

    function setTimeOut( $ts_time_out ) {
        $ts_time_out = (int) $ts_time_out;
        if ( $this->ts_time_out != $ts_time_out ) {
            $this->ts_time_out = $ts_time_out;
            $this->saved = false;
        }
    }

    function setSource( $ts_source ) {
        if ( !in_array( $ts_source, array( 'totem', 'web', 'app' ) ) ) {
            $ts_source = "totem";
        }
        if ( $this->ts_source != $ts_source ) {
            $this->saved = false;
            $this->ts_source = $ts_source;
        }
    }

    function setOpCode( $ts_op_code ) {
        if ( $this->ts_op_code != $ts_op_code ) {
            $this->ts_op_code = $ts_op_code;
            $this->saved = false;
        }
    }

    function setIsTrash( $ts_is_trash ) {
        $ts_is_trash = ( $ts_is_trash ) ? 1 : 0;
        if ( $this->ts_is_trash != $ts_is_trash ) {
            $this->ts_is_trash = $ts_is_trash;
            $this->saved = false;
        }
    }

    function setDeskNumber( $ts_desk_number ) {
        $ts_desk_number = (int) $ts_desk_number;
        if ( $this->ts_desk_number != $ts_desk_number ) {
            $this->ts_desk_number = $ts_desk_number;
            $this->saved = false;
        }
    }

}
