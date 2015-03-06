var count = 5;
var usePause = true;
$( function () {
	var disabled = $( 'div#disableNextButton' ).text();
	if ( disabled.indexOf("true") > -1 ) {
		$( 'input[name="next"]' ).prop( 'disabled', true );
		usePause = !$( 'input[name="pause"]' ).prop( 'disabled' );
		if ( usePause ) {
			$( 'input[name="pause"]' ).prop( 'disabled', true );
		}
		timerOp();
	}
} );

function timerOp() {
	if ( count > 0 ) {
		$( 'input[name="next"]' ).val( 'Prossimo (' + count + ')' );
		count--;
		setTimeout( timerOp, 1000 );
	} else {
		$( 'input[name="next"]' ).val( 'Prossimo' );
		$( 'input[name="next"]' ).prop( 'disabled', false );
		if ( usePause ) {
			$( 'input[name="pause"]' ).prop( 'disabled', false );
		}
		count = 5;
	}
}
