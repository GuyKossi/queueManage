<?php

class AppGetOfficeListByGps extends AppAction {

	public $willWriteDatabase = false;

	public function execute() {
		if ( empty( $_POST['latitude'] ) || empty( $_POST['longitude'] ) ) {
			throw new InvalidParamException();
		}
		$lat = (double) $_POST['latitude'];
		$lon = (double) $_POST['longitude'];
		$result = Office::fromDatabaseSearchByCoords( $lat, $lon );
		$content = array();
		$content['NumOffices'] = count( $result );
		$content['Offices'] = array();
		foreach ( $result as $office ) {
			$officeObj = array(
				'Code' => $office['of_code'],
				'Name' => $office['of_name'],
				'City' => $office['of_city'],
				'Address' => $office['of_address'],
				'Host' => $office['of_host'],
				'Distance' => round ( $office['of_distance'], 2 ),
			);
			$content['Offices'][] = $officeObj;
		}

		return $content;
	}
}