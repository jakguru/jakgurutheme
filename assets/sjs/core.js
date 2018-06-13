@import "../../node_modules/sprintf-js/src/sprintf.js";
@import "../../node_modules/moment/min/moment-with-locales.js";

jQuery( '.custom-logo-link' ).on( 'click', function( e ) {
	e.preventDefault();
});

var updateClock = function() {
	jQuery( '.sysui-clock' ).text( moment().utc().format( app.moment.timeformat ) );
}

updateClock();
setInterval( updateClock, 1000 );