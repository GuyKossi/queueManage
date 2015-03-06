<?php

class AppNoSuchAction extends AppAction {

	public $willWriteDatabase = false;

	public function execute() {
		return array(
			"ErrorCode" => "AE???",
			"ErrorMsg" => "No such action"
		);
	}
}
