<?php

/**
 * Description of Operator
 *
 * @author sergio
 */
class Operator extends DatabaseTableWithId {
    
    /**
     * @var array
     */
    protected $columns = array(
        'op_code',
        'op_name',
        'op_surname',
        'op_password',
    );
    
    /**
     * @var string
     */
    protected $idColumn = 'op_id';
    
    /**
     * @var string
     */
    protected $tableName = 'operator';
    
    protected $op_id;
    protected $op_code;
    protected $op_name;
    protected $op_surname;
    protected $op_password;
    
    protected function __construct() {
        // Private constructor (use static methods)
    }
    
    protected static function newFromDatabaseRow( $row ) {
        return parent::newFromDatabaseRowGeneric( $row, 'Operator' );
    }
    
    public static function fromDatabaseById( $op_id ) {
        return parent::fromDatabaseByParameterGeneric( 'op_id', $op_id, 'Operator' );
    }
    
    public static function fromDatabaseByCode( $op_code ) {
        return parent::fromDatabaseByParameterGeneric( 'op_code', $op_code, 'Operator' );
    }
    
    /**
     * @param string $op_code
     * @param string $password
     * @return boolean
     */
    public static function isValidLogin( $op_code, $password ) {
        $op_password = sha1( $op_code . $password );
        $sql = "SELECT op_code FROM operator WHERE op_code=? AND op_password=?";
        $stmt = Database::prepareStatement( $sql );
        $stmt->execute( array( $op_code, $op_password ) );
        $result = $stmt->fetch( PDO::FETCH_ASSOC );
        if ( $result ) {
            return true;
        }
        return false;
    }
    
    public static function fromDatabaseCompleteList() {
        $sql = "SELECT * FROM operator ORDER BY op_code";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute() ) {
            throw new Exception( __METHOD__
                    . " Error while reading operator from database" );
        }
        $output = array();
        $rows = $stmt->fetchall( PDO::FETCH_ASSOC );
        foreach ($rows as $row) {
            $output[] = self::newFromDatabaseRow( $row );
        }
        return $output;
    }
    
    /**
     * @return Operator
     */
    public static function newRecord() {
        $ret = new Operator();
        $ret->saved = false;
        $ret->isNew = true;
        
        return $ret;
    }
    
    public function getId() {
        return $this->op_id;
    }

    public function getCode() {
        return $this->op_code;
    }

    public function getName() {
        return $this->op_name;
    }

    public function getSurname(){
        return $this->op_surname;
    }

    public function getFullName() {
        return $this->op_name . " " . $this->op_surname;
    }

    public function getPasswordHash() {
        return $this->op_password;
    }

    public function setCode( $op_code ) {
        if ( $op_code != $this->op_code ) {
            $this->saved = false;
            $this->op_code = $op_code;
        }
    }

    public function setName( $op_name ) {
        if ( $op_name != $this->op_name ) {
            $this->saved = false;
            $this->op_name = $op_name;
        }
    }

    public function setSurname( $op_surname ) {
        if ( $op_surname != $this->op_surname ) {
            $this->saved = false;
            $this->op_surname = $op_surname;
        }
    }

    public function setPassword( $newPassword ) {
        if ( !isset( $this->op_code ) ) {
            throw new Exception( __METHOD__
                    . 'Operator code must be known before setting password' );
        }
        $op_password = sha1( $this->op_code . $newPassword );
        if ( $op_password != $this->op_password ) {
            $this->saved = false;
            $this->op_password = $op_password;
        }
    }

    public function isOnline() {
        global $gvSessionTimeout;

        $desk = Desk::fromDatabaseByOperator( $this->op_code );
        $ticket = Ticket::fromDatabaseByOperator( $this->op_code );
        if ( !$desk && !$ticket ) {
            // Offline
            return false;
        }
        if ( $desk ) {
            $last = $desk->getLastActivityTime();
            if ( time() - $last >= $gvSessionTimeout ) {
                // Session timed out, update table
                self::clearTableForLogout( $this->op_code, $desk, $ticket );
                return false;
            }
        }

        return true;
    }

    // Provide desk or ticket objects if known to avoid to read again database
    public static function clearTableForLogout( $op_code, $desk = null, $ticket = null ) {
        if ( !$desk ) {
            // Load desk
            $desk = Desk::fromDatabaseByOperator( $op_code );
        }
        if ( !$ticket ) {
            // Load ticket
            $ticket = Ticket::fromDatabaseByOperator( $op_code );
        }
        if ( $desk ) {
            $desk->setLastActivityTime( null );
            $desk->setOpCode( null );
            $desk->save();
        }
        if ( $ticket ) {
            $stats = TicketStats::newFromTicket( $ticket );
            $ticket->delete();
            $stats->save();
        }

    }
}
