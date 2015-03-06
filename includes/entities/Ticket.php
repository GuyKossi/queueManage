<?php
/**
 * This class models a ticket both in ticket_in
 * or ticket_exec.
 *
 * @author sergio
 */
class Ticket extends DatabaseTable {

	// Ticket_in fields
	public $id = null; // integer auto_increment
	public $code; // char(1)
	public $number; // integer
	public $time_in; // integer (timestamp)
	public $source; // varchar(10) 'totem', 'web', 'app'
	public $source_id = ''; // varchar(50)
	public $notice_counter = -1; // integer (won't be retained in ticket_exec)

	// Ticket_exec fields
	public $time_exec = null; // integer (timestamp)
	public $op_code = null; // varchar(30)
	public $desk_number = null; // tinyint

	// Internal variables
	public $table = 'ticket_in'; // ticket_in or ticket_exec

	private function __construct() {
		// Private constructor (use static methods)
	}

	// Getters

	public function getId() {
		return $this->id;
	}

	public function getCode() {
		return $this->code;
	}

	public function getNumber() {
		return $this->number;
	}

	public function getTextString() {
		$str = (string) $this->number;
		$str = str_repeat( '0', 3 - strlen( $str ) ) . $str;
		$str = $this->code . $str;
		return $str;
	}

	public function getTimeIn() {
		return $this->time_in;
	}

	public function getSource() {
		return $this->source;
	}

	public function getSourceId() {
		return $this->source_id;
	}

	public function getNoticeCounter() {
		return $this->notice_counter;
	}

	public function getTimeExec() {
		return $this->time_exec;
	}

	public function getOpCode() {
		return $this->op_code;
	}

	public function getDeskNumber() {
		return $this->desk_number;
	}

	public function getTable() {
		return $this->table;
	}

	// Return array with (peopleBefore and eta in secs)
	public function getPeopleAndEta() {
		if ( $this->table != "ticket_in" ) {
			gfDebug( __METHOD__ .
				" Warning! Requested ETA for a ticket not in queue." );
			return array( 0, 0 );
		}
		$td = TopicalDomain::fromDatabaseByCode( $this->getCode() );
		if ( !$td ) {
			gfDebug( __METHOD__ .
				" Warning! Unable to compute ticket ETA." );
			return array( 0, 0 );
		}
		$peopleBefore = self::countTicketBefore(
			$this->getCode(), $this->getNumber()
		);
		$people = self::getNumberTicketInQueue( $this->getCode() );
		$eta = (int) ( $td->getEta() / $people ) * $peopleBefore;
		return array( $peopleBefore, $eta );
	}

	public function isSaved() {
		return $this->saved;
	}

	public function isNew() {
		return $this->isNew;
	}

	public function save() {
        if ( $this->saved ) {
            return true;
        }
        
        $idColumn = "id";
        // Common columns
        $columns = array(
        	"code",
        	"number",
        	"time_in",
        	"source",
        	"source_id",
        );
        if ( $this->table == 'ticket_in' ) {
        	$columns[] = "notice_counter" ;
        } else {
        	$columns = array_merge( $columns, array(
        		"time_exec",
        		"op_code",
        		"desk_number",
        	) );
        }

        $p = $this->table == 'ticket_in' ? 'ti_' : 'te_';
        $prefIdColumn = $p . $idColumn;
        $prefColumns = array_map(
        	function ( $col ) use ( $p ) {
        		return $p . $col;
        	}
        	, $columns
        );

        $parameters = array();
        foreach ( $columns as $column ) {
            $parameters[] = $this->$column;
        }
        
        if ( $this->isNew ) {
            $sql = "INSERT INTO $this->table ("
            	. implode( ",", $prefColumns )
            	. ") VALUES ("
                . str_repeat("?,", count( $prefColumns ) - 1 ) . "?)";
        } else {
            $sql = "UPDATE $this->table SET ";
            $sqlColumns = "";
            foreach ( $prefColumns as $column ) {
                if ( empty( $sqlColumns ) ) {
                    // First iteration
                    $sqlColumns = "$column=?";
                } else {
                    $sqlColumns .= ",$column=?";
                }
            }
            $sql .= $sqlColumns;
            $sql .= " WHERE $prefIdColumn=?";
            $parameters[] = $this->$idColumn;
        }
        $conn = Database::getConnection();
        $stmt = $conn->prepare( $sql );
        $result = $stmt->execute( $parameters );
        
        if ( $this->isNew ) {
            // Database has assigned an ID for the new desk
            $this->$idColumn = $conn->lastInsertId();
        }
        
        if ( !$result ) {
            return false;
        }
        $this->saved = true;
        $this->isNew = false;
        return true;
    }

	// Setters

	public function setCode( $code ) {
		$code = strtoupper( $code );
		if ( $this->code != $code ) {
			$this->saved = false;
			$this->code = $code;
		}
	}

	public function setNumber( $number ) {
		$number = (int) $number;
		if ( $this->number != $number ) {
			$this->saved = false;
			$this->number = $number;
		}
	}

	public function setTimeIn( $time_in ) {
		$time_in = (int) $time_in;
		if ( $this->time_in != $time_in ) {
			$this->saved = false;
			$this->time_in = $time_in;
		}
	}

	public function setSource( $source ) {
		if ( !in_array( $source, array( 'totem', 'web', 'app' ) ) ) {
			$source = "totem";
		}
		if ( $this->source != $source ) {
			$this->saved = false;
			$this->source = $source;
		}
	}

	public function setSourceId( $source_id ) {
		$source_id = (string) $source_id;
		if ( $this->source_id != $source_id ) {
			$this->saved = false;
			$this->source_id = $source_id;
		}
	}

	public function setNoticeCounter( $notice_counter ) {
		$notice_counter = (int) $notice_counter;
		if ( $this->notice_counter != $notice_counter ) {
			$this->saved = false;
			$this->notice_counter = $notice_counter;
		}
	}

	public function setTimeExec( $time_exec ) {
		$time_exec = (int) $time_exec;
		if ( $this->time_exec != $time_exec ) {
			$this->saved = false;
			$this->time_exec = $time_exec;
		}
	}

	public function setOpCode( $op_code ) {
		if ( $this->op_code != $op_code ) {
			$this->saved = false;
			$this->op_code = $op_code;
		}
	}

	public function setDeskNumber( $desk_number ) {
		$desk_number = (int) $desk_number;
		if ( $this->desk_number != $desk_number ) {
			$this->saved = false;
			$this->desk_number = $desk_number;
		}
	}

	public function moveToNextTable(
		$op_code, $desk_number, $time_exec = null
	) {
		if ( $this->isNew ) {
			throw new Exception(  __METHOD__ .
				" Error: cannot move new ticket"
			);
		}
		if ( !$time_exec ) {
			$time_exec = time();
		}
		$this->delete();
		$this->isNew = true;
		$this->saved = false;
		$this->table = 'ticket_exec';
		$this->setOpCode( $op_code );
		$this->setDeskNumber( $desk_number );
		$this->setTimeExec( $time_exec );

		// Update topical domain Eta
		$eta = $this->getTimeExec() - $this->getTimeIn();
		$td = TopicalDomain::fromDatabaseByCode( $this->getCode() );
		if ( !$td ) {
			throw new Exception( __METHOD__ . " Unable to read TopicalDomain");
		}
		$oldEta = $td->getEta();
		if ( !$oldEta ) {
			// oldEta is 0, do not count in average
			$oldEta = $eta;
		}
		// If 0 tickets in queue, set Eta back to 0
		if ( self::getNumberTicketInQueue( $this->getCode() ) == 0 ) {
			$newEta = 0;
		} else {
			$newEta = (int) ( ( $oldEta + $eta ) / 2 );
		}
		$td->setEta( $newEta );
		$td->save();
	}

	public function delete() {
		if ( $this->isNew ) {
            throw new Exception( __METHOD__ .
            	' Cannot delete new record' );
        }
        $idColumn = ( $this->table == "ticket_in" ? 'ti_' : 'te_' ) . 'id';
        $sql = "DELETE FROM $this->table WHERE $idColumn=?";
        $stmt = Database::prepareStatement( $sql );
        $result = $stmt->execute( array( $this->id ) );
        if ( !$result ) {
            //Deletion failed
            return false;
        }
        return true;
	}

	// For app requests which want to cancel the ticket
	public function cancel() {
		if ( $this->table != 'ticket_in' ) {
			throw new Exception( __METHOD__ .
				" Only ticket in queue can be canceled" );
		}
		if ( !$this->delete() ) {
			return false;
		}
		$ts = $this->getTicketStats();
		$ts->save();
		$this->decrementNoticeCounter();
		self::sendNotices();

		return true;
	}

	public function getTicketStats() {
		return TicketStats::newFromTicket( $this );
	}

	// This will decrement notice counter starting from "this"
	// ticket (excluded)
	public function decrementNoticeCounter() {
		$sql = "UPDATE ticket_in " .
			"SET ti_notice_counter = ti_notice_counter - 1 " .
			"WHERE ti_code = ? AND ti_number > ? AND ti_notice_counter >= 0";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $this->code, $this->number ) ) ) {
			throw new Exception( "Unable to decrement notice counters" );
		}
	}

	public static function newRecord( $table = 'ticket_in' ) {
		$ticket = new Ticket();
		$ticket->table = $table;
		$ticket->isNew = true;
		$ticket->saved = false;
		return $ticket;
	}

	public static function nextNewTicket(
		$td_code, $source = 'totem', $source_id = '', &$noticeBefore = -1
	) {
		if ( !in_array( $source, array( 'totem', 'web', 'app' ) ) ) {
			throw new Exception( __METHOD__ . " Invalid source value" );
		}
		if ( $source == 'totem' ) {
			$noticeBefore = -1;
			$source_id = '';
		} else if ( !$source_id ) {
			throw new Exception( __METHOD__
				. " web and app tickets need a source_id");
		}
		$ticket = self::newRecord( 'ticket_in' );
		$ticket->setCode( $td_code );
        $ticket->setTimeIn( time() );
		$ticket->setSource( $source );
		$ticket->setSourceId( $source_id );

		// Check existence of topical domain
		$td = TopicalDomain::fromDatabaseByCode( $td_code );
		if ( !$td || !$td->getActive() ) {
			throw new Exception( __METHOD__ . " td_code not valid" );
		}
		$number = $td->incrementNextGeneratedTicket();
		$td->save();
		$ticket->setNumber( $number );

		if ( $source != 'totem' ) {
			$ticketBefore = self::countTicketBefore( $td_code, $number );
			if ( $source == 'web' ) {
				$ticket->setNoticeCounter( (int) ( $ticketBefore / 2 ) );
			} else {
				$ticket->setNoticeBeforeForApp( $noticeBefore, $ticketBefore );
			}
		}
		return $ticket;
	}

	public function setNoticeBeforeForApp( &$noticeBefore, $ticketBefore = null ) {
		global $gvQueueLengthAppLimit;
		if ( $noticeBefore <= 0 ) {
			throw new Exception( __METHOD__
				. " noticeBefore not valid for app ticket" );
		}
		if ( is_null( $ticketBefore ) ) {
			$ticketBefore = self::countTicketBefore( $this->getCode(), $this->getNumber() );
		}
		if ( $noticeBefore > $ticketBefore ) {
			$noticeBefore = $ticketBefore;
			$counter = 1;
		} elseif ( $noticeBefore < $gvQueueLengthAppLimit
			&& $ticketBefore > $gvQueueLengthAppLimit
		) {
			$noticeBefore = $gvQueueLengthAppLimit;
			$counter = $ticketBefore - $gvQueueLengthAppLimit;
		} else {
			$counter = $ticketBefore - $noticeBefore;
		}
		$this->setNoticeCounter( $counter );
	}

	public static function fromDatabase( $td_code, $ticket_number, $table ) {
		$td_code = strtoupper( $td_code );
		$ticket_number = (int) $ticket_number;
		if ( $table != 'ticket_in' ) {
			$table = 'ticket_exec';
		}
		$p = $table == 'ticket_in' ? 'ti_' : 'te_';
		$sql = "SELECT * FROM $table WHERE "
		. "${p}code = ? AND ${p}number = ? LIMIT 1";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $td_code, $ticket_number ) ) ) {
			throw new Exception( __METHOD__ .
                " Error while reading ticket from database" );
		}
		$row = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( !$row ) {
			return null;
		}
		return self::newFromDatabaseRow( $row, $table );
	}

	private static function getNextTicket( $topicalDomains = false ) {
		$sql = "SELECT * FROM ticket_in ";
		if ( $topicalDomains ) {
			$sql .= "WHERE ti_code IN ($topicalDomains) ";
		}
		$sql .= "ORDER BY ti_time_in, ti_number LIMIT 1";
		$conn = Database::getConnection();
		if ( !( $stmt = $conn->query( $sql ) ) ) {
			throw new Exception( __METHOD__ .
				" Error while getting the ticket to be served");
		}
		return $stmt->fetch( PDO::FETCH_ASSOC );
	}

	public static function serveNextTicket(
		$topicalDomains, $op_code, $desk_number
	) {
		if ( !$topicalDomains ) {
			throw new Exception( __METHOD__ .
				" Provided empty topicalDomains array" );
		}
		$topicalDomains = (array) $topicalDomains;
		$desk_number = (int) $desk_number;
		$conn = Database::getConnection();
		$topicalDomains = array_map( 'strtoupper', $topicalDomains );
		$topicalDomains = array_map(
			array( $conn, 'quote' ),
			$topicalDomains
		);
		$topicalDomains = implode( ",", $topicalDomains );
		$row = self::getNextTicket( $topicalDomains );
		if ( !$row ) {
			global $gvCallOtherTdWhenEmpty;
			if ( !$gvCallOtherTdWhenEmpty ) {
				// No ticket to be served
				return null;
			}
			// Try again with all topical domains
			$row = self::getNextTicket( false );
			if ( !$row ) {
				// No ticket at all in the queue
				return null;
			}
		}
		$ticket = self::newFromDatabaseRow( $row, 'ticket_in' );
		$ticket->decrementNoticeCounter();
		self::sendNotices();
		$ticket->sendYourTurn();

		$ticket->moveToNextTable( $op_code, $desk_number );
		$ticket->save();

		// Add ticket to display_main table
		DisplayMain::addRecord( $ticket );

		return $ticket;
	}

	protected static function sendNotices() {
		$tickets = self::getTicketForNotice();
		$jobs = JobQueue::getInstance();
		foreach ( $tickets as $ticket ) {
			list( $peopleBefore, $eta ) = $ticket->getPeopleAndEta();
			$eta = (int) ( $eta / 60 );
			$sourceId = $ticket->getSourceId();
			$class = $ticket->getSource() == "app" ?
				"AppPushSender" : "SmsSender";
			$call = function () use ( $peopleBefore, $eta, $sourceId, $class ) {
				$sender = new $class( $sourceId );
				$sender->sendNotice( $peopleBefore, $eta );
			};
			$jobs->addJob( $call );

			// Decrement counter to mark notice has already been sent
			$ticket->setNoticeCounter( $ticket->getNoticeCounter() - 1 );
			$ticket->save();
		}
	}

	public static function getTicketForNotice() {
		$sql = "SELECT * FROM ticket_in WHERE "
			. "ti_notice_counter = 0 AND ti_source IN ('app', 'web')";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute() ) {
			throw new Exception( __METHOD__ .
                " Error while reading ticket from database" );
		}
		$rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
		$ret = array();
		foreach ( $rows as $row ) {
			$ret[] = self::newFromDatabaseRow( $row, 'ticket_in' );
		}
		return $ret;
	}

	protected function sendYourTurn() {
		if ( $this->getSource() != 'app' ) {
			return;
		}
		/*
		 * Hack: cannot pass $this to closures.
		 * They are objects and define their
		 * own $this.
		 */
		$ref = $this;
		$call = function () use ( $ref ) {
			$sender = new AppPushSender( $ref->getSourceId() );
			$sender->sendYourTurn( $ref );
		};
		$jobs = JobQueue::getInstance();
		$jobs->addJob( $call );
	}

	public static function fromDatabaseByDesk( $desk_number ) {
		$desk_number = (int) $desk_number;
		$sql = "SELECT * FROM ticket_exec WHERE "
			. "te_desk_number = ? LIMIT 1";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $desk_number ) ) ) {
			throw new Exception( __METHOD__ .
                " Error while reading ticket from database" );
		}
		$row = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( !$row ) {
			return null;
		}
		return self::newFromDatabaseRow( $row, 'ticket_exec' );
	}

	public static function fromDatabaseBySourceId( $source_id ) {
		$sql = "SELECT * FROM ticket_in WHERE "
			. "ti_source != 'totem' AND ti_source_id = ? LIMIT 1";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $source_id ) ) ) {
			throw new Exception( __METHOD__ .
                " Error while reading ticket from database" );
		}
		$row = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( !$row ) {
			return null;
		}
		return self::newFromDatabaseRow( $row, 'ticket_in' );
	}

	public static function fromDatabaseByOperator( $op_code ) {
		$sql = "SELECT * FROM ticket_exec WHERE "
			. "te_op_code = ? LIMIT 1";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $op_code ) ) ) {
			throw new Exception( __METHOD__ .
                " Error while reading ticket from database" );
		}
		$row = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( !$row ) {
			return null;
		}
		return self::newFromDatabaseRow( $row, 'ticket_exec' );
	}
        
    public static function getNumberTicketInQueue( $td_code ) {
    	$td_code = strtoupper( $td_code );
        $sql = "SELECT COUNT(ti_id) FROM ticket_in WHERE ti_code = ?";
        $stmt = Database::prepareStatement( $sql );
        if ( !$stmt->execute( array( $td_code ) ) ) {
        	throw new Exception( __METHOD__ .
                " Error while counting ticket in queue" );
        }
        $row = $stmt->fetch();
		if ( !$row ) {
			return 0;
		}
		return (int) $row[0];
    }

	protected static function newFromDatabaseRow( $row, $table = 'ticket_in' ) {
		if ( $table != 'ticket_in' ) {
			$table = 'ticket_exec';
		}
		$p = $table == 'ticket_in' ? 'ti_' : 'te_';
		$ret = new Ticket();
		$ret->id = $row[$p . 'id'];
		$ret->code = $row[$p . 'code'];
		$ret->number = $row[$p . 'number'];
		$ret->time_in = $row[$p . 'time_in'];
		$ret->source = $row[$p . 'source'];
		$ret->source_id = $row[$p . 'source_id'];
		$ret->table = $table;
		if ( $table == 'ticket_in' ) {
			$ret->notice_counter = $row[$p . 'notice_counter'];
		} else {
			$ret->time_exec = $row[$p . 'time_exec'];
			$ret->op_code = $row[$p . 'op_code'];
			$ret->desk_number = $row[$p . 'desk_number'];
		}
		$ret->saved = true;
		$ret->isNew = false;
		return $ret;
	}

	protected static function countTicketBefore( $code, $number ) {
		$code = strtoupper( $code );
		$number = (int) $number;
		$sql = "SELECT COUNT( ti_id ) FROM ticket_in "
		. "WHERE ti_code = ? AND ti_number < ?";
		$stmt = Database::prepareStatement( $sql );
		if ( !$stmt->execute( array( $code, $number ) ) ) {
			throw new Exception( __METHOD__ . "Unable to count ticket" );
		}
		$result = $stmt->fetch( PDO::FETCH_BOTH );
		return $result[0];
	}
}
