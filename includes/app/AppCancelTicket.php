<?php

class AppCancelTicket extends AppAction {

	public $willWriteDatabase = true;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode;

		if ( empty( $_POST['ticket'] ) ) {
			throw new InvalidParamException();
		} else {
			$ticket = $_POST['ticket'];
		}

		$ticket = $this->getTicket( $ticket );
		if ( $ticket->cancel() ) {
			return array(
				'OfficeCode' => $gvOfficeCode,
				'Status' => 'success'
			);
		} else {
			// Trigger internal error
			throw new Exception( __METHOD__ .
				" Ticket::cancel() returned false" );
		}
	}
}
