$( function() {
	$( 'tr.clickable' ).on( 'click', function ( ev ) {
		var td_code = $( this ).attr( 'data-code' );
		// Insert form and submit
		var form = $( '<form method="post" style="display: none;"></form>' );
		var input = $( '<input type="hidden" name="td_code" value="' + td_code + '" />' );
		input.appendTo( form );
		// Firefox require the form is in the DOM to be submitted
		form.appendTo( 'body' );
		form.submit();
	} );
} );