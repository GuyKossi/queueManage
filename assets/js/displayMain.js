var entries = [];
var bell = null;
var lastTicket = 'A000';
var td_code = '';

var url = '../direct/displayMainPolling.php';
$( function () {
	td_code = getParameterByName( 'td_code' );
	setUpSound();
	doPoll();
} );

String.prototype.repeat = function( num ) {
    return new Array( num + 1 ).join( this );
}

function getParameterByName( name ) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function doPoll() {
	$.ajax( {
		url: url,
		type: 'GET',
		dataType: 'json',
		timeout: 300000,
		data: {
			'lastTicket': lastTicket,
			'td_code': td_code
		},
		success: function ( data ) {
			if ( data.status === 'unchanged' ) {
				return;
			}
			if ( data.status === 'redirect' ) {
				window.location.href = data.location;
				return;
			}
			entries = data.entries.concat( entries );
			if ( entries.length > 8 ) {
				entries = entries.slice( 0, 8 );
			}

			lastTicket = entries[0].ticket;

			bell.currentTime = 0;
			bell.play();
			updateInterface();
		},
		complete: doPoll
	} );
}

function updateInterface() {
	// updateInterface is invoked when at least
	// one ticket is present

	// Remove all paragraph
	$( 'div' ).remove();

	// Reinsert paragraphs
	var i;
	for ( i = 0; i < entries.length; i++ ) {
		var text = entries[i].ticket + ' - ' + entries[i].desk;
		$( '<div class="displayMain"><span>' + text + '</span></div>' ).appendTo( 'body' );
	};
	blinkLastTicket();
}

function blinkLastTicket() {
	$( 'body div:first-child' )
	.animate({'opacity': "0"}, 400)
    .animate({'opacity': "1"}, 400)
    .animate({'opacity': "0"}, 400)
    .animate({'opacity': "1"}, 400);
}

function setUpSound() {
        bell = document.createElement('audio');
        // FIXME path hardcoded here, ugly!
        bell.setAttribute('src', '/fastqueue/assets/audio/bell.ogg');
}
