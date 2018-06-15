var sysuiwindow = function( args ) {
	var properties = {
		icon: '',
		title: '',
		minimize: true,
		maximize: true,
		close: true,
		menubars: [],
		content: '',
		onOpen: function(){},
		onClose: function(){},
		autoopen: true,
	}
	for ( var arg in properties ) {
		if ( typeof( args[ arg ] ) == typeof( properties[ arg ] ) ) {
			properties[ arg ] = args[ arg ];
		}
	}
	var obj = this;
	this.open = function() {

	}
	this.close = function() {

	}
	this.maximize = function() {

	}
	this.minimize = function() {
		
	}
	return obj;
}