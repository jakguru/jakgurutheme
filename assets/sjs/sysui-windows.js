var sysuiwindow = function( args ) {
	var random_id = function() {
		return sprintf( 'sysuiw_%s_%s', moment().format( 'x' ), Math.floor( Math.random() * 100000 ) );
	}
	var get_z_index = function() {
		var z = 1;
		jQuery( '.sysui-window' ).each( function() {
			var zi = parseFloat( jQuery( this ).css('z-index') );
			if ( typeof( zi ) !== 'number' ) {
				zi = 1;
			}
			if ( zi >= z ) {
				z = zi;
			}
		});
		z = z + 1;
		return z;
	}
	var get_initial_position = function( width, height ) {
		var top = 0;
		var left = 0;
		var windowHight = jQuery( window ).height();
		windowHight = windowHight - 30;
		var windowWidth = jQuery( window ).width();
		if ( width < windowWidth ) {
			left = ( windowWidth / 2 ) - ( width / 2 ) - 1;
		}
		if ( height < windowHight ) {
			top = ( windowHight / 2 ) - ( height / 2 ) - 1;
		}
		return {
			top: top,
			left: left,
		}
	}
	var asset_path = function( asset ) {
		return sprintf( '%s%s', app.asset_path, asset );
	}
	var get_min_width = function() {
		var min = 18;
		if ( properties.minimize ) {
			min += 18;
		}
		if ( properties.maximize ) {
			min += 18;
		}
		if ( properties.close ) {
			min += 18;
		}
		min += 8;
		return min;
	}
	var rightmouseheld = false;
	var rightmouseoffset = {
		top: 0,
		left: 0,
	}
	var bottommouseheld = false;
	var bottommouseoffset = {
		top: 0,
		left: 0,
	}
	var resizemouseheld = false;
	var resizemouseoffset = {
		top: 0,
		left: 0,
	}
	var topmouseheld = false;
	var topmouseoffset = {
		top: 0,
		left: 0,
	}
	var windowTemplate = '<div id="%s" class="sysui-panel-outer sysui-window sysui-window-loading"><div class="sysui-panel-inner"><div class="sysui-window-titlebar"><span class="sysui-window-titlebar-icon"><img src="%s" /></span><span class="sysui-window-titlebar-title">%s</span></div><div class="sysui-window-menubar-wrapper"></div><div class="sysui-panel-window-content">%s</div><div class="sysui-window-right-side"></div><div class="sysui-window-bottom-side"></div><div class="sysui-window-resize-control"></div></div></div>';
	var taskbarButtonTemplate = '<div class="sysui-button-outer sysui-taskbar-program" for="%s"><div class="sysui-button-inner"><span class="sysui-taskbar-program-icon"><img src="%s" /></span><span class="sysui-taskbar-program-title">%s</span></div></div>';
	var properties = {
		icon: asset_path( 'images/defaultapp.png' ),
		title: 'A System Window',
		minimize: true,
		maximize: true,
		close: true,
		menus: [
			{
				title: 'File',
				items: [{title: 'Close',href:'#',class:'sysui-close-window'}],
			}
		],
		content: '<p>You need to overwrite the content</p>',
		onOpen: function(){},
		onClose: function(){},
		autoopen: true,
		width: 500,
		height: 300,
		maximized: false,
		page_id: 0,
		permalink: '',
	}
	for ( var arg in properties ) {
		if ( 'object' == typeof( args ) && typeof( args[ arg ] ) == typeof( properties[ arg ] ) ) {
			properties[ arg ] = args[ arg ];
		}
	}
	var obj = this;
	this.id = random_id();
	this.open = function() {
		var zi = get_z_index( obj.id );
		var pos = get_initial_position( properties.width, properties.height );
		var html = sprintf(
			windowTemplate,
			obj.id,
			properties.icon,
			properties.title,
			properties.content
		);
		var sysuiwindow = jQuery( html );
		sysuiwindow.attr( 'page-id', properties.page_id );
		sysuiwindow.attr( 'permalink', properties.permalink );
		sysuiwindow.css({
			top: pos.top,
			left: pos.left,
			'z-index': zi,
		});
		sysuiwindow.children( '.sysui-panel-inner' ).css({
			width: properties.width,
			height: properties.height,
			'min-width': get_min_width(),
			'min-height': 44,
		});
		if ( true == properties.minimize ) {
			var btnhtml = sprintf(
				'<button class="sysui-button-outer sysui-minimize-window" title="%s"><div class="sysui-button-inner sysui-window-titlebar-control-wrapper"><img src="%s" class="sysui-window-titlebar-control" /></div></button>',
				app.terms.minimize,
				asset_path( 'images/minimize.png' )
			);
			var btn = jQuery( btnhtml );
			sysuiwindow.find( '.sysui-window-titlebar' ).append( btn );
		}
		if ( true == properties.maximize ) {
			var btnhtml = sprintf(
				'<button class="sysui-button-outer sysui-maximize-window" title="%s"><div class="sysui-button-inner sysui-window-titlebar-control-wrapper"><img src="%s" class="sysui-window-titlebar-control" /></div></button>',
				app.terms.maximize,
				asset_path( 'images/maximize.png' )
			);
			var btn = jQuery( btnhtml );
			sysuiwindow.find( '.sysui-window-titlebar' ).append( btn );
		}
		if ( true == properties.close ) {
			var btnhtml = sprintf(
				'<button class="sysui-button-outer sysui-close-window" title="%s"><div class="sysui-button-inner sysui-window-titlebar-control-wrapper"><img src="%s" class="sysui-window-titlebar-control" /></div></button>',
				app.terms.close,
				asset_path( 'images/close.png' )
			);
			var btn = jQuery( btnhtml );
			sysuiwindow.find( '.sysui-window-titlebar' ).append( btn );
		}
		if ( 0 == properties.menus.length ) {
			sysuiwindow.find( '.sysui-window-menubar-wrapper' ).remove();
		}
		else {
			var menuhtml = '<div class="sysui-window-menubar">';
			for (var i = 0; i < properties.menus.length; i++) {
				var m = properties.menus[i];
				menuhtml += '<div class="sysui-window-menu">';
				menuhtml += sprintf( '<div class="sysui-window-menu-title">%s</div>', m.title );
				menuhtml += '<div class="sysui-window-menu-dropdown sysui-panel-outer">';
				menuhtml += '<div class="sysui-panel-inner">';
				for (var mi = 0; mi < m.items.length; mi++) {
					var menuitem = m.items[mi];
					menuhtml += sprintf( '<a href="%s" class="sysui-window-menu-dropdown-item %s">%s</a>', menuitem.href, menuitem.class, menuitem.title );
				}
				menuhtml += '</div>';
				menuhtml += '</div>';
				menuhtml += '</div>';
			}
			menuhtml += '</div>';
			var menus = jQuery( menuhtml );
			menus.find( '.sysui-panel-inner' ).css({
				'z-index': zi + 1,
			})
			sysuiwindow.find( '.sysui-window-menubar-wrapper' ).html( menus );
		}
		sysuiwindow.find( '.sysui-minimize-window' ).on( 'click', obj.minimize );
		sysuiwindow.find( '.sysui-maximize-window' ).on( 'click', obj.maximize );
		sysuiwindow.find( '.sysui-close-window' ).on( 'click', obj.close );
		sysuiwindow.find( '.sysui-submit-window-form' ).on( 'click', function( e ) {
			e.preventDefault();
			sysuiwindow.find( 'form' ).each( function() {
				var form = jQuery( this );
				form.submit();
			});
		});
		sysuiwindow.removeClass( 'sysui-window-loading' );
		sysuiwindow.sysuiwindow = obj;
		jQuery( 'body' ).append( sysuiwindow );
		jQuery( document ).on( 'mouseup', function( e ) {
			if ( sysuiwindow.is( e.target ) || sysuiwindow.has( e.target ).length > 0 ) {
				obj.focus();
			}
			else {
				obj.blur();
			}
		});
		sysuiwindow.find( '.sysui-window-titlebar' ).on( 'mousedown', function( e ) {
			obj.focus();
			topmouseheld = true;
			topmouseoffset.left = e.pageX;
			topmouseoffset.top = e.pageY;
		});
		sysuiwindow.find( '.sysui-window-right-side' ).on( 'mousedown', function( e ) {
			obj.focus();
			rightmouseheld = true;
			rightmouseoffset.left = e.pageX;
			rightmouseoffset.top = e.pageY;
		});
		sysuiwindow.find( '.sysui-window-bottom-side' ).on( 'mousedown', function( e ) {
			obj.focus();
			bottommouseheld = true;
			bottommouseoffset.left = e.pageX;
			bottommouseoffset.top = e.pageY;
		});
		sysuiwindow.find( '.sysui-window-resize-control' ).on( 'mousedown', function( e ) {
			obj.focus();
			rightmouseheld = true;
			rightmouseoffset.left = e.pageX;
			rightmouseoffset.top = e.pageY;
			bottommouseheld = true;
			bottommouseoffset.left = e.pageX;
			bottommouseoffset.top = e.pageY;
		});
		jQuery( document ).on( 'mouseup', function( e ) {
			topmouseheld = false;
			rightmouseheld = false;
			bottommouseheld = false;
			resizemouseheld = false;
		});
		jQuery( document ).on( 'mousemove', function( e ) {
			if ( topmouseheld ) {
				var leftoffset = topmouseoffset.left - e.pageX;
				var topoffset = topmouseoffset.top - e.pageY;
				var currentTop = parseFloat( sysuiwindow.css('top') );
				var currentLeft = parseFloat( sysuiwindow.css('left') );
				var ncss = {
					top: currentTop + ( -1 * topoffset ),
					left: currentLeft + ( -1 * leftoffset ),
				};
				sysuiwindow.css( ncss );
				topmouseoffset.left = e.pageX;
				topmouseoffset.top = e.pageY;
			}
			if ( rightmouseheld || resizemouseheld ) {
				var leftoffset = e.pageX - rightmouseoffset.left;
				var currentWidth = parseFloat( sysuiwindow.children( '.sysui-panel-inner' ).css( 'width' ) );
				var ncss = {
					width: currentWidth + leftoffset,
				}
				sysuiwindow.children( '.sysui-panel-inner' ).css( ncss );
				rightmouseoffset.left = e.pageX;
			}
			if ( bottommouseheld || resizemouseheld ) {
				var topoffset = e.pageY - bottommouseoffset.top;
				var currentHight = parseFloat( sysuiwindow.children( '.sysui-panel-inner' ).css( 'height' ) );
				var ncss = {
					height: currentHight + topoffset,
				}
				sysuiwindow.children( '.sysui-panel-inner' ).css( ncss );
				bottommouseoffset.top = e.pageY;
			}
		});
		var taskbarbuttonhtml = sprintf( taskbarButtonTemplate, obj.id, properties.icon, properties.title );
		var taskbarbutton = jQuery( taskbarbuttonhtml );
		jQuery( '#sysui-taskbar-programs-wrapper' ).append( taskbarbutton );
		taskbarbutton.on( 'click', obj.focus );
		taskbarbutton.on( 'rightclick', obj.focus );
		if ( true == properties.maximized ) {
			obj.maximize();	
		}
		// sysui-password-form
		properties.onOpen( obj );
		obj.focus();
	}
	this.focus = function() {
		var sw = jQuery( '#' + obj.id );
		jQuery( '.sysui-window' ).each( function() {
			var w = jQuery( this );
			w.removeClass( 'focused' );
			jQuery( '#sysui-taskbar-programs-wrapper' ).find( '[for="' + w.attr( 'id' ) + '"]').removeClass( 'active' );	
		});
		sw.addClass( 'focused' );
		var zi = get_z_index();
		if ( zi >= 1079 ) {
			jQuery( '.sysui-window' ).each( function() {
				var osw = jQuery( this ),
					ozi = parseFloat( osw.css('z-index') );
					if ( ozi <= 1 || ozi == 'auto' ) {
						ozi = 2;
					}
					osw.css({'z-index':ozi - 1});
			});
		}
		zi = get_z_index();
		sw.css({
			'z-index': zi,
		});
		jQuery( '#sysui-taskbar-programs-wrapper' ).find( '[for="' + obj.id + '"]').addClass( 'active' );
		if ( ! sw.is( ':visible' ) ) {
			obj.restore();
		}
	}
	this.blur = function() {
		var sw = jQuery( '#' + obj.id );
		sw.removeClass( 'focused' );
		jQuery( '#sysui-taskbar-programs-wrapper' ).find( '[for="' + obj.id + '"]').removeClass( 'active' );
	}
	this.close = function() {
		jQuery( '#' + obj.id ).remove();
		jQuery( '#sysui-taskbar-programs-wrapper' ).find( '[for="' + obj.id + '"]').remove();
		properties.onClose();
	}
	this.maximize = function() {
		jQuery( '#' + obj.id ).attr( 'original-top', jQuery( '#' + obj.id ).css( 'top' ) );
		jQuery( '#' + obj.id ).attr( 'original-left', jQuery( '#' + obj.id ).css( 'left' ) );
		jQuery( '#' + obj.id ).css({
			top: 0,
			left: 0,
		});
		jQuery( '#' + obj.id ).attr( 'original-width', jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css('width') );
		jQuery( '#' + obj.id ).attr( 'original-height', jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css('height') );
		jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css({
			width: jQuery( window ).width(),
			height: jQuery( window ).height() - 30,
		});
		jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).off( 'click' );
		jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).on( 'click', obj.restore );
		jQuery( '#' + obj.id ).find( '.sysui-maximize-window img' ).attr( 'src', asset_path( 'images/restore.png' ) );
		jQuery( '#' + obj.id ).focus();
	}
	this.minimize = function() {
		jQuery( '#' + obj.id ).css({
			display: 'none',
		});
		jQuery( '#' + obj.id ).attr( 'original-width', jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css('width') );
		jQuery( '#' + obj.id ).attr( 'original-height', jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css('height') );
	}
	this.restore = function() {
		if ( ! jQuery( '#' + obj.id ).is(':visible' ) ) {
			jQuery( '#' + obj.id ).css({
				display: 'block',
			});
		}
		else {
			jQuery( '#' + obj.id ).css({
				top: jQuery( '#' + obj.id ).attr( 'original-top' ),
				left: jQuery( '#' + obj.id ).attr( 'original-left' ),
			});
			jQuery( '#' + obj.id ).children( '.sysui-panel-inner' ).css({
				width: jQuery( '#' + obj.id ).attr( 'original-width' ),
				height: jQuery( '#' + obj.id ).attr( 'original-height' ),
			});
			jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).off( 'click' );
			jQuery( '#' + obj.id ).find( '.sysui-maximize-window' ).on( 'click', obj.maximize );
			jQuery( '#' + obj.id ).find( '.sysui-maximize-window img' ).attr( 'src', asset_path( 'images/maximize.png' ) );
			jQuery( '#' + obj.id ).focus();
		}	
	}
	if ( true == properties.autoopen ) {
		obj.open();
	}
	return obj;
}