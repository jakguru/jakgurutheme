@import "../../node_modules/sprintf-js/src/sprintf.js";
@import "../../node_modules/moment/min/moment-with-locales.js";

jQuery( document ).ready(function() {
	document.oncontextmenu = function( e ) {
		jQuery( document ).trigger( 'rightclick', e );
		jQuery( window ).trigger( 'rightclick', e );
		jQuery( e.target ).trigger( 'rightclick', e );
		//return false;
	};
	document.body.onselectstart = function() {
		return false;
	}
});

jQuery( '.custom-logo-link' ).on( 'click', function( e ) {
	e.preventDefault();
});

var update_clock = function() {
	jQuery( '.sysui-clock' ).text( moment().utc().format( app.moment.timeformat ) );
}

var fix_start_menu_os_identifier_height = function() {
	var height = jQuery( '#sysui-os-identifier-rotated-content-inner' )[0].scrollWidth;
	jQuery( '#sysui-os-identifier' ).css({
		'min-height': ( height + 2 ),
	});
	jQuery( '#sysui-os-identifier-rotated-content-inner' ).css({
		top: ( height + 2 ),
	});
}

var handle_start_button_click = function( e ) {
	e.preventDefault();
	if ( ! jQuery( '#start-menu' ).is(':visible') ) {
		jQuery( '#start-menu' ).addClass( 'active' );
		jQuery( '#sysui-start' ).addClass( 'active' );
		fix_start_menu_os_identifier_height();
	}
	else {
		close_start_menu();
	}
}

var close_start_menu = function() {
	jQuery( '#start-menu' ).removeClass( 'active' );
	jQuery( '#sysui-start' ).removeClass( 'active' );
}

var handle_desktop_click = function( e ) {
	if ( jQuery( '#start-menu' ).is(':visible') ) {
		var startmenu = jQuery( '#start-menu' ),
			startbutton = jQuery( '#sysui-start' ),
			tgt = e.target;
		if (
			! startbutton.is( tgt )
			&& ! startmenu.is( tgt )
			&& startmenu.has( tgt ).length === 0
		) {
			close_start_menu();
		}
	}
}

var handle_link_click = function( e ) {
	var link = jQuery( this );
	var wploginpath = sprintf( '%s/wp-login.php',app.site_path ),
		wpadminpath = sprintf( '%s/wp-admin/',app.site_path )
	if (
		'undefined' !== typeof( link.attr( 'href' ) )
		&& '#' !== link.attr( 'href' )
		&& link.attr( 'href' ).startsWith( app.site_path )
		&& ! link.attr( 'href' ).startsWith( wploginpath )
		&& ! link.attr( 'href' ).startsWith( wpadminpath )
	) {
		e.preventDefault();
		if ( link.attr( 'href' ) == app.site_path + '/' ) {
			jQuery( '.sysui-minimize-window' ).each( function() {
				jQuery( this ).click();
			});
		}
		else {
			var query = link.attr( 'href' ).substring( app.site_path.length );
			open_page_by_query( query );
		}
	}
}

var open_page_by_query = function( query ) {
	jQuery.ajax({
		async: true,
		cache: false,
		crossDomain: false,
		data: {
			action: 'page_request',
			query: query,
		},
		success: function( data, textStatus, jqXHR ) {
			if ( true == data.success ) {
				data.data.onOpen = function( obj ) {
					jQuery( '#' + obj.id ).find( 'a' ).on( 'click', handle_link_click );
				}
				new sysuiwindow( data.data );
			}
			else {
				console.log( data );
			}
			close_start_menu();
		},
		method: 'POST',
		url: app.ajax_url,
	});
}

update_clock();
setInterval( update_clock, 1000 );
fix_start_menu_os_identifier_height();
jQuery( '#sysui-start' ).on( 'click', handle_start_button_click );
jQuery( document ).on( 'mouseup', handle_desktop_click );
jQuery( 'a' ).on( 'click', handle_link_click );
jQuery( '.sysui-activate-seach' ).on( 'click', function( e ) {
	e.preventDefault();
	app.defaultwindows.search.onOpen = function( obj ) {
		jQuery( '#' + obj.id ).find( 'a' ).on( 'click', handle_link_click );
	}
	new sysuiwindow( app.defaultwindows.search );
	close_start_menu();
});