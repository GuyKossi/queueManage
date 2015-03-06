var ticketCode, ticketNumber, deskNumber, url;		
$( function () {
	ticketCode = $( 'div#displayTicketCode' ).text();
	ticketNumber = $( 'div#displayTicketNumber' ).text();
	deskNumber = $( 'div#displayTicketDesk' ).text();
	url = '../direct/displayDeskPolling.php';		
	doPoll();
} );

String.prototype.repeat = function( num ) {
    return new Array( num + 1 ).join( this );
}

function doPoll() {
	$.ajax( {
		url: url,
		type: 'GET',
		dataType: 'json',
		data: {
			ticketCode: ticketCode,
			ticketNumber: ticketNumber,
			deskNumber: deskNumber
		},
		timeout: 300000,
		success: function ( data ) {
			if ( data.status === 'unchanged' ) {
				return;
			}
			if ( data.status === 'redirect' ) {
				window.location.href = data.location;
				return;
			}
			if ( data.code ) {
				ticketCode = data.code;
				ticketNumber = String( data.number );
				text = data.code + "0".repeat( 3 - ticketNumber.length ) + ticketNumber;
			} else {
				// Desk closed
				ticketCode = '';
				ticketNumber = '';
				text = 'Chiuso';
			}
			$( 'div#servedNumber' ).text( text );
		},
		complete: doPoll
	} );
}