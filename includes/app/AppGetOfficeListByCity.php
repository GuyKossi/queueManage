<?php

class AppGetOfficeListByCity extends AppAction {

	public $willWriteDatabase = false;

	public function execute() {
		if ( empty( $_POST['city'] ) ) {
			throw new InvalidParamException();
		}
		$result = Office::fromDatabaseSearchByCity( $_POST['city'] );
		$content = array();
		$content['NumOffices'] = count( $result );
		$content['Offices'] = array();
		foreach ( $result as $office ) {
			$officeObj = array(
				'Code' => $office->getCode(),
				'Name' => $office->getName(),
				'City' => $office->getCity(),
				'Address' => $office->getAddress(),
				'Host' => $office->getHost()
			);
			$content['Offices'][] = $officeObj;
		}

		return $content;
	}
}