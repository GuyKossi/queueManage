$( function () {
	if ( $( 'select#dev_desk_number' ).val() != '0' ) {
		$( 'select#dev_td_code' ).prop( 'disabled', true );
	}
	$( 'select#dev_desk_number' ).change( function () {
		if ( $( this ).val() == '0' ) {
			$( 'select#dev_td_code' ).prop( 'disabled', false );
		} else {
			$( 'select#dev_td_code' ).val( 0 );
			$( 'select#dev_td_code' ).prop( 'disabled', true );
		}
	} );
} );
