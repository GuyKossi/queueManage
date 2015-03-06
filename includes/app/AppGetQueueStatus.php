<?php

class AppGetQueueStatus extends AppAction {

	public $willWriteDatabase = false;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode;

		$tdList = TopicalDomain::fromDatabaseCompleteList();
		$content = array();
		if ( !$tdList ) {
			$content['ErrorCode'] = "AE003";
			$content['ErrorMsg'] = "No queues in this office";
			return $content;
		}
		$content['OfficeCode'] = $gvOfficeCode;
		$content['NumQueues'] = count( $tdList );
		$content['Queues'] = array();
		foreach ( $tdList as $td ) {
			$queueObj = array(
				'Code' => $td->getCode(),
				'Name' => $td->getName(),
				'Description' => $td->getDescription(),
				'Eta' => (int) ( $td->getEta() / 60 ),
				'PeopleWaiting' => Ticket::getNumberTicketInQueue( $td->getCode() )
			);
			$content['Queues'][] = $queueObj;
		}

		return $content;
	}
}