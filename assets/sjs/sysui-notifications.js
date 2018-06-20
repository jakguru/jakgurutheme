var sysuinotification = function( args ) {
     var random_id = function() {
          return sprintf( 'sysuin_%s_%s', moment().format( 'x' ), Math.floor( Math.random() * 100000 ) );
     }
     var asset_path = function( asset ) {
          return sprintf( '%s%s', app.asset_path, asset );
     }
     var properties = {
          icon: asset_path( 'images/info.png' ),
          title: '',
          content: 'Something Happened which required a notification',
          timeout: 60000,
          onShow: function( obj ) {},
          autoload: true
     }
     for ( var arg in properties ) {
          if ( 'object' == typeof( args ) && typeof( args[ arg ] ) == typeof( properties[ arg ] ) ) {
               properties[ arg ] = args[ arg ];
          }
     }
     var obj = this;
     var html = sprintf( '<a href="#" class="sysui-notification-link"><span><img src="%s" /></span></a>', properties.icon );
     this.id = random_id();
     this.notification = jQuery( html );
     this.icon = this.notification.find( 'img' );
     this.visible = false;
     this.init = function() {
          jQuery( '#sysui-notifications > nav' ).prepend( obj.notification );
          jQuery( obj.icon ).popover({
               container: '#sysui-taskbar',
               title: properties.title,
               content: properties.content,
               placement: 'top',
               trigger: 'hover',
               template: sprintf( '<div class="popover %s" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>', obj.id ),
          });
          jQuery( obj.icon ).on( 'inserted.bs.popover', function( e ) {
               obj.notification.on( 'click', obj.close );
               jQuery( '.' + obj.id ).on( 'click', obj.close );
               properties.onShow( obj );
          });
          jQuery( obj.icon ).popover( 'show' );
          setTimeout( function() {
               jQuery( obj.icon ).popover( 'hide' );
          }, 3000 );
          setTimeout( obj.close, properties.timeout );
          obj.visible = true;
     }
     this.close = function() {
          if ( true !== obj.visible ) {
               return;
          }
          jQuery( obj.icon ).popover('dispose');
          obj.notification.remove();
          obj.visible = false;
     }
     if ( true == properties.autoload ) {
          obj.init();
     }
}