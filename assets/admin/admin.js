var init_menu_image_field = function() {
	var wrapper = jQuery( this );
	if ( wrapper.hasClass( 'initialized' ) ) {
		return;
	}
	wrapper.find( '.image-preview' ).css({
		'background-image': 'url(' + wrapper.find( 'input' ).val() + ')',
	});
	wrapper.find( '.refresh-preview' ).on( 'click', function( e ) {
		e.preventDefault();
		wrapper.find( '.image-preview' ).css({
			'background-image': 'url(' + wrapper.find( 'input' ).val() + ')',
		});
	});
	wrapper.find( 'input' ).on( 'change', function( e ) {
		wrapper.find( '.image-preview' ).css({
			'background-image': 'url(' + wrapper.find( 'input' ).val() + ')',
		});
	});
	wrapper.find( '.open-image-media-manager' ).on( 'click', function( e ) {
		e.preventDefault();
		if ( wrapper.mediamanager ) {
			wrapper.mediamanager.open();
			return;
		}
		wrapper.mediamanager = wp.media.frames.file_frame = wp.media({
			title: jgt.terms.chooseicon,
			button: {
				text: jgt.terms.chooseicon,
			},
			multiple: false,
			library: {
				type: ['image'],
			}
		});
		wrapper.mediamanager.on( 'select', function() {
			attachment = wrapper.mediamanager.state().get('selection').first().toJSON();
			var url = attachment.url;
			url = url.replace( window.location.origin, '' );
			wrapper.find( 'input' ).val( url );
			wrapper.find( 'input' ).trigger( 'change' );
		});
		wrapper.mediamanager.open();
	});
	wrapper.addClass( 'initialized' );
}

/**
 * TODO: Replace this with a hook which is triggered every time a new menu item is added
 */
setInterval( function() {
	jQuery( '.menu-image-field-wrapper' ).each( init_menu_image_field );
}, 100 );