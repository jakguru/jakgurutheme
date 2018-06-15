<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');
get_header();

?>
<div id="test-window" class="sysui-panel-outer sysui-window" style="top: 30px; left: 30px;">
	<div class="sysui-panel-inner" style="width: 500px; height: 300px;">
		<div class="sysui-window-titlebar">
			<span class="sysui-window-titlebar-icon">
				<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/defaultapp.png' ) ); ?>" />
			</span>
			<span class="sysui-window-titlebar-title">Test Window</span>
			<button class="sysui-button-outer" title="Minimize">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/minimize.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
			<button class="sysui-button-outer" title="Maximize">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/maximize.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
			<button class="sysui-button-outer" title="Close">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/close.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
		</div>
		<div class="sysui-window-menubar-wrapper">
			<div class="sysui-window-menubar">
				<div class="sysui-window-menu">
					<div class="sysui-window-menu-title">File</div>
					<div class="sysui-window-menu-dropdown sysui-panel-outer">
						<div class="sysui-panel-inner">
							<a href="#" class="sysui-window-menu-dropdown-item">Close</a>
						</div>
					</div>
				</div>
				<div class="sysui-window-menu">
					<div class="sysui-window-menu-title">Edit</div>
					<div class="sysui-window-menu-dropdown sysui-panel-outer">
						<div class="sysui-panel-inner">
							<a href="#" class="sysui-window-menu-dropdown-item">Close</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="sysui-panel-window-content"></div>
		<div class="sysui-window-right-side"></div>
		<div class="sysui-window-bottom-side"></div>
		<div class="sysui-window-resize-control"></div>
	</div>
</div>
<div id="test-window-2" class="sysui-panel-outer sysui-window focused" style="top: 360px; left: 30px;">
	<div class="sysui-panel-inner" style="width: 500px; height: 300px;">
		<div class="sysui-window-titlebar">
			<span class="sysui-window-titlebar-icon">
				<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/defaultapp.png' ) ); ?>" />
			</span>
			<span class="sysui-window-titlebar-title">Test Window 2</span>
			<button class="sysui-button-outer" title="Minimize">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/minimize.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
			<button class="sysui-button-outer" title="Maximize">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/maximize.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
			<button class="sysui-button-outer" title="Close">
				<div class="sysui-button-inner sysui-window-titlebar-control-wrapper">
					<img src="<?php echo esc_url( Theme_Utils::asset_path( 'images/close.png' ) ); ?>" class="sysui-window-titlebar-control" />
				</div>
			</button>
		</div>
		<div class="sysui-window-menubar-wrapper">
			<div class="sysui-window-menubar">
				<div class="sysui-window-menu">
					<div class="sysui-window-menu-title">File</div>
					<div class="sysui-window-menu-dropdown sysui-panel-outer">
						<div class="sysui-panel-inner">
							<a href="#" class="sysui-window-menu-dropdown-item">Close</a>
						</div>
					</div>
				</div>
				<div class="sysui-window-menu">
					<div class="sysui-window-menu-title">Edit</div>
					<div class="sysui-window-menu-dropdown sysui-panel-outer">
						<div class="sysui-panel-inner">
							<a href="#" class="sysui-window-menu-dropdown-item">Close</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="sysui-panel-window-content"></div>
		<div class="sysui-window-right-side"></div>
		<div class="sysui-window-bottom-side"></div>
		<div class="sysui-window-resize-control"></div>
	</div>
</div>
<?php

get_footer();