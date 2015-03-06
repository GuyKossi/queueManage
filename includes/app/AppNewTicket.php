<?php

class AppNewTicket extends AppAction {

	public $willWriteDatabase = true;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode, $gvQueueLengthAppLimit, $gvQueueEtaAppLimit;

		if ( empty( $_POST['queueCode'] )
			|| empty( $_POST['noticeBefore'] ) ) {
			throw new InvalidParamException();
		} else {
			$td_code = $_POST['queueCode'];
			$noticeBefore = $_POST['noticeBefore'];
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

		$td = TopicalDomain::fromDatabaseByCode( $td_code );
		if ( !$td || !$td->getActive() ) {
			return array(
				'ErrorCode' => "AE003",
				'ErrorMsg' => "Queue specified does not exist"
			);
		}

		if (
			$td->getEta() < $gvQueueEtaAppLimit
			|| Ticket::getNumberTicketInQueue( $td_code ) < $gvQueueLengthAppLimit
		) {
			return array(
				'ErrorCode' => "AE???",
				'ErrorMsg' => "Queue limit not satisfied"
			);
		}

		$ticket = Ticket::nextNewTicket( $td_code, 'app', $this->token, $noticeBefore );
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
