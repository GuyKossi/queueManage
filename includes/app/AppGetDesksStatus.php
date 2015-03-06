<?php

class AppGetDesksStatus extends AppAction {

	public $willWriteDatabase = false;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode;

		$deskList = Desk::fromDatabaseCompleteList();
		$content = array();
		if ( !$deskList ) {
			$content['ErrorCode'] = "AE004";
			$content['ErrorMsg'] = "No desks in this office";
			return $content;
		}
		$content['OfficeCode'] = $gvOfficeCode;
		$content['NumDesks'] = count( $deskList );
		$content['Desks'] = array();
		foreach ( $deskList as $desk ) {
			if ( $desk->getOpCode() != null ) {
				$ticket = Ticket::fromDatabaseByDesk( $desk->getNumber() );
				if ( $ticket ) {
					$ticket = $ticket->getTextString();
				} else {
					$ticket = '';
				}
			} else {
				$ticket = '';
			}
			$deskObj = array(
				'Number' => $desk->getNumber(),
				'Ticket' => $ticket,
			);
			$content['Desks'][] = $deskObj;
		}

		return $content;
	}
}