@import "../../node_modules/sprintf-js/src/sprintf.js";
@import "../../node_modules/moment/min/moment-with-locales.js";
@import "../../node_modules/js-cookie/src/js.cookie.js";

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
	var query = window.location.href.substring( app.site_path.length );
	open_page_by_query( query );
	History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
		var State = History.getState(); // Note: We are using History.getState() instead of event.state
	});
	show_cookie_policy_notification();
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

var open_page_by_query = function( query, password ) {
	jQuery.ajax({
		async: true,
		cache: false,
		crossDomain: false,
		data: {
			action: 'page_request',
			query: query,
			password: ( 'string' == typeof( password ) ) ? password : '',
		},
		success: function( data, textStatus, jqXHR ) {
			if ( true == data.success ) {
				data.data.onOpen = function( obj ) {
					if ( 'undefined' !== typeof( data.data.base_query ) && 'undefined' !== typeof( data.data.base_query.s ) ) {
						obj.searchterm = data.data.base_query.s;
					}
					if ( 'string' == typeof( jQuery( '#' + obj.id ).find( '[name="s"]' ).val() ) && jQuery( '#' + obj.id ).find( '[name="s"]' ).val().length == 0 ) {
						jQuery( '#' + obj.id ).find( '[name="s"]' ).val( obj.searchterm );
					}
					setTimeout( function(){
						jQuery( '#' + obj.id ).find( 'a' ).on( 'click', handle_link_click );
						jQuery( '#' + obj.id ).find( 'form.sysui-password-form' ).on( 'submit', function( e ) {
							e.preventDefault();
							var form = jQuery( this ),
								sq = form.find( '[name="query"]' ).val(),
								pw = form.find( '[name="password"]' ).val();
							obj.close();
							open_page_by_query( sq, pw );
						});
						jQuery( '#' + obj.id ).find( 'form.sysui-search-left-panel-form' ).on( 'submit', function( e ) {
							e.preventDefault();
							jQuery.ajax({
								async: true,
								cache: false,
								crossDomain: false,
								data: {
									action: 'search_query_request',
									s: jQuery( this ).find( '[name="s"]' ).val(),
								},
								success: function( redata, textStatus, jqXHR ) {
									if ( true == redata.success ) {
										var c = jQuery( redata.data.content );
										jQuery( '#' + obj.id ).attr( 'page-id', redata.data.page_id );
										jQuery( '#' + obj.id ).attr( 'permalink', redata.data.permalink );
										jQuery( '#' + obj.id ).find( '.sysui-window-titlebar-title' ).html( redata.data.title );
										jQuery( '[for="' + obj.id + '"] .sysui-taskbar-program-title' ).html( redata.data.title );
										update_url_and_title( redata.data.permalink, redata.data.title );
										jQuery( '#' + obj.id ).find( '.sysui-window-list' ).html( c.html() );
										jQuery( '#' + obj.id ).find( '.sysui-minimize-window' ).off( 'click' );
										jQuery( '#' + obj.id ).find( '.sysui-minimize-window' ).on( 'click', obj.minimize );
										jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).off( 'click' );
										jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).on( 'click', obj.maximize );
										jQuery( '#' + obj.id ).find( '.sysui-close-window' ).off( 'click' );
										jQuery( '#' + obj.id ).find( '.sysui-close-window' ).on( 'click', obj.close );
										jQuery( '#' + obj.id ).find( '.sysui-submit-window-form' ).off( 'click' );
										jQuery( '#' + obj.id ).find( '.sysui-submit-window-form' ).on( 'click', function( e ) {
											e.preventDefault();
											sysuiwindow.find( 'form' ).each( function() {
												var form = jQuery( this );
												form.submit();
											});
										});
										if ( 'undefined' !== typeof( redata.data.base_query ) && 'undefined' !== typeof( redata.data.base_query.s ) ) {
											obj.searchterm = redata.data.base_query.s;
											jQuery( '#' + obj.id ).find( '[name="s"]' ).val( obj.searchterm );
										}
										if ( redata.data.current_items < redata.data.expected_items ) {
											obj.populate_paged_items( redata.data.base_query, 2, redata.data.current_items, redata.data.expected_items );
										}
										else {
											obj.triggerOpened();
										}
									}
								},
								method: 'POST',
								url: app.ajax_url,
							});
						});
					},100 );
					jQuery( '#' + obj.id ).find( 'a' ).off( 'click' );
					jQuery( '#' + obj.id ).find( 'form.sysui-password-form' ).off( 'submit' );
					jQuery( '#' + obj.id ).find( 'form.sysui-search-left-panel-form' ).off( 'submit' );
				}
				new sysuiwindow( data.data );
			}
			close_start_menu();
		},
		method: 'POST',
		url: app.ajax_url,
	});
}

var update_url_and_title = function( url, title ) {
	if ( 'string' !== typeof( url ) || url.length == 0 || url == '/' ) {
		url = app.site_path + '/';
	}
	if ( 'string' !== typeof( title ) ) {
		title = jQuery( 'head>title' ).html();
	}
	History.replaceState({state:3}, sprintf( app.title_format, title ), url );
}

var show_cookie_policy_notification = function() {
	if ( true !== app.legal.show_cookie_policy_notification ) {
		return;
	}
	var accepted_cookie_policy = Cookies.get( 'accepted_cookie_policy' );
	if ( 'undefined' == typeof( accepted_cookie_policy ) ) {
		var html = sprintf( '<a href="%s" class="sysui-notification-link"><span><img src="%s" /></span></a>', app.legal.privacy_policy_url, app.asset_path + 'images/info.png' );
		var notification = jQuery( html );
		jQuery( '#sysui-notifications > nav' ).prepend( notification );
		var image = notification.find( 'img' );
		jQuery( image ).popover({
			container: '#sysui-taskbar',
			content: app.legal.cookie_policy_notification_text,
			placement: 'top',
			trigger: 'manual',
			template: sprintf( '<div class="popover cookie-popover" role="tooltip" title="%s"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>', app.legal.click_to_dismiss ),
		});
		jQuery( image ).on( 'inserted.bs.popover', function( e ) {
			jQuery( '.cookie-popover' ).on( 'click', function( e ) {
				e.preventDefault();
				Cookies.set( 'accepted_cookie_policy', 'yes' );
				jQuery( image ).popover('dispose');
				notification.remove();
			});
		});
		jQuery( image ).popover('show');
	}
}

update_clock();
setInterval( update_clock, 1000 );
fix_start_menu_os_identifier_height();
jQuery( '#sysui-start' ).on( 'click', handle_start_button_click );
jQuery( document ).on( 'mouseup', handle_desktop_click );
jQuery( 'a' ).on( 'click', handle_link_click );