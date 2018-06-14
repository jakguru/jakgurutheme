@import "../../node_modules/bootstrap/dist/js/bootstrap.bundle.js";

jQuery( '.sysui-notification-link' ).each( function() {
	var link = jQuery( this ),
		image = link.find( 'img' ),
		text = image.attr( 'data-notification-text' );
	jQuery( image ).popover({
		container: link.closest( 'header' ),
		content: text,
		placement: 'top',
		trigger: 'hover focus',
	});
});