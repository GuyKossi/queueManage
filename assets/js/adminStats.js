$( function () {
	$( ".datepicker" ).datepicker()
		.datepicker( "option", "dateFormat", 'dd-mm-yy' )
		.datepicker( "option", "maxDate", new Date() )
		;
} );