<?php

class AppDigitalize extends AppAction {

	public $willWriteDatabase = true;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode, $gvOfficeSecret;

		if (
			empty( $_POST['ticket'] )
			|| empty( $_POST['noticeBefore'] )
			|| empty( $_POST['verificationCode'] )
		) {
			throw new InvalidParamException();
		} else {
			$ticket = $_POST['ticket'];
			$noticeBefore = $_POST['noticeBefore'];
			$verificationCode = $_POST['verificationCode'];
		}

		if ( $noticeBefore <= 0 ) {
			throw new InvalidParamException();
		} else {
			$noticeBefore = (int) $noticeBefore;
		}

		if ( Ticket::fromDatabaseBySourceId( $this->token ) ) {
			return array(
				'ErrorCode' => "AE???",
				'ErrorMsg' => "Ticket already exists for this token"
			);
		}

		$hash = sha1( $gvOfficeCode . $ticket . $gvOfficeSecret );
		$hash = substr( $hash, 0, 20 );
		$hash = strtoupper( $hash );
		if ( $hash != $verificationCode ) {
			return array(
				'ErrorCode' => "AE???",
				'ErrorMsg' => "Verification code not valid"
			);
		}

		$ticket_code = substr( $ticket, 0, 1 );
		$ticket_num = (int) substr( $ticket, 1 );

		$ticket = Ticket::fromDatabase( $ticket_code, $ticket_num, 'ticket_in' );
		if ( !$ticket ) {
			return array(
				'ErrorCode' => "AE???",
				'ErrorMsg' => "Ticket does not exist"
			);
		}

		if ( $ticket->getSource() != 'totem' ) {
			return array(
				'ErrorCode' => "AE???",
				'ErrorMsg' => "Ticket is not from totem"
			);
		}

		$ticket->setNoticeBeforeForApp( $noticeBefore );
		$ticket->setSource( 'app' );
		$ticket->setSourceId( $this->token );
		$ticket->save();

		list( $people, $eta ) = $ticket->getPeopleAndEta();

		$content = array();
		$content['OfficeCode'] = $gvOfficeCode;
		$content['Ticket'] = $ticket->getTextString();
		$content['Eta'] = (int) ( $eta / 60 );
		$content['PeopleBefore'] = $people;
		$content['AssignedNoticeBefore'] = $noticeBefore;
		return $content;
	}
}
