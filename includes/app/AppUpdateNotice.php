<?php

class AppUpdateNotice extends AppAction {

	public $willWriteDatabase = true;
	public $requireOfficeCode = true;

	public function execute() {
		global $gvOfficeCode, $gvQueueLengthAppLimit;

		if ( empty( $_POST['ticket'] )
			|| empty( $_POST['noticeBefore'] ) ) {
			throw new InvalidParamException();
		} else {
			$ticket = $_POST['ticket'];
			$noticeBefore = $_POST['noticeBefore'];
		}

		if ( $noticeBefore <= 0 ) {
			throw new InvalidParamException();
		} else {
			$noticeBefore = (int) $noticeBefore;
		}

		$ticket = $this->getTicket( $ticket );
		list( $people, $eta ) = $ticket->getPeopleAndEta();
		if ( $people <= $gvQueueLengthAppLimit ) {
			return array(
				'ErrorCode' => "AE008",
				'ErrorMsg' => "Unable to set new noticeBefore"
			);
		}
		$ticket->setNoticeBeforeForApp( $noticeBefore, $people );
		$ticket->save();

		return array(
			'OfficeCode' => $gvOfficeCode,
			'AssignedNoticeBefore' => $noticeBefore
		);
	}
}
