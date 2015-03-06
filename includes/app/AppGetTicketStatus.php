<?php

class AppGetTicketStatus extends AppAction {

	public $willWriteDatabase = false;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode;

		if ( empty( $_POST['ticket'] ) ) {
			throw new InvalidParamException();
		} else {
			$ticket = $_POST['ticket'];
		}

		$ticket = $this->getTicket( $ticket );
		list( $people, $eta ) = $ticket->getPeopleAndEta();
		$eta = (int) ( $eta / 60 );
		$noticeBefore = $people - $ticket->getNoticeCounter();

		return array(
			'OfficeCode' => $gvOfficeCode,
			'Ticket' => $ticket->getTextString(),
			'ETA' => $eta,
			'PeopleBefore' => $people,
			'noticeBefore' => $noticeBefore
		);

	}
}
