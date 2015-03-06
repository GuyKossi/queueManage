<?php

abstract class AppAction {

	public static $actions = array(
		'cancelticket' => 'AppCancelTicket',
		'digitalizeticket' => 'AppDigitalize',
		'getdesksstatus' => 'AppGetDesksStatus',
		'getofficelistbycity' => 'AppGetOfficeListByCity',
		'getofficelistbygps' => 'AppGetOfficeListByGps',
		'getqueuestatus' => 'AppGetQueueStatus',
		'getticketstatus' => 'AppGetTicketStatus',
		'newticket' => 'AppNewTicket',
		'updatenotice' => 'AppUpdateNotice',
	);

	public $willWriteDatabase = true;
	public $requireOfficeCode = false;
	public $token = null;

	public function __construct( $token ) {
		global $gvOfficeCode;

		if ( !$this->willWriteDatabase ) {
			Database::lockTables( false );
		}
		if ( !self::isValidToken( $token ) ) {
			throw new InvalidTokenException();
		}
		$this->token = $token;

		if ( $this->requireOfficeCode ) {
			if (
				!isset( $_POST['officeCode'] )
				|| $gvOfficeCode != $_POST['officeCode']
			) {
				throw new InvalidOfficeCodeException();
			}
		}
		if ( $this->isBanned() ) {
			throw new BannedDeviceException();
		}
	}

	// Return content to be sent as Json
	public abstract function execute();

	public function isBanned() {
		return Ban::isBanned( $this->token );
	}

	public function getTicket( $ticketStr ) {
		if ( strlen( $ticketStr ) != 4 ) {
			throw new InvalidParamException();
		}
		$ticketCode = $ticketStr[0];
		$ticketNum = (int) substr( $ticketStr, 1 );
		$ticket = Ticket::fromDatabase( $ticketCode, $ticketNum, 'ticket_in' );
		if ( !$ticket ) {
			throw new InvalidTicketException();
		}
		if ( $this->token != $ticket->getSourceId() ) {
			throw new BadOwnershipException();
		}
		return $ticket;
	}

	public static function isValidToken( $token ) {
		// TODO: to implement
		return true;
	}

	public static function getClass( $actionName ) {
		$actionName = strtolower( $actionName );
		if ( array_key_exists( $actionName , self::$actions ) ) {
			return self::$actions[$actionName];
		} else {
			return 'AppNoSuchAction';
		}
	}
}
