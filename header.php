<?php defined('ABSPATH') || die('Sorry, but you cannot access this page directly.'); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="format-detection" content="telephone=no">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<header class="sysui-panel-outer" id="sysui-taskbar">
			<div class="sysui-panel-inner">
				<div id="sysui-taskbar-outer">
					<div id="sysui-taskbar-start-wrapper">
						<button id="sysui-start" class="sysui-button-outer">
							<div class="sysui-button-inner">
								<div id="sysui-start-icon">
								<?php
								if ( function_exists( 'the_custom_logo' ) ) {
									the_custom_logo();
								}
								?>
								</div>
								<div id="sysui-start-content"><?php bloginfo( 'name', 'display' ); ?></div>
							</div>
						</button>
					</div>
					<div class="start-panel-seperator-wrapper">
						<div class="start-panel-seperator-relic"></div>
					</div>
					<?php
					if ( true == get_theme_mod( 'enable_quicklaunch', true ) && has_nav_menu( 'quick_links' ) ) {
					?>
					<div id="sysui-taskbar-quick-launch-wrapper">
						<div id="sysui-quick-launch-area">
						<?php wp_nav_menu( array(
							'theme_location' => 'quick_links',
							'container' => false,
							'menu_class' => 'sysui-quick-links d-none d-sm-inline-block',
							'depth' => 1,
							'items_wrap' => '<nav id="%1$s" class="%2$s">%3$s</nav>',
							'walker' => new Quick_Links_Nav_Walker(),
						) ); ?>
						</div>
					</div>
					<div class="start-panel-seperator-wrapper d-none d-sm-inline-block">
						<div class="start-panel-seperator-relic"></div>
					</div>
					<?php
					}
					?>
					<div id="sysui-taskbar-programs-wrapper"></div>
					<div id="sysui-taskbar-notifications-wrapper">
						<div id="sysui-notifications">
							<?php wp_nav_menu( array(
								'theme_location' => 'notification_area',
								'container' => false,
								'menu_class' => 'sysui-notifications-links',
								'depth' => 1,
								'items_wrap' => '<nav id="%1$s" class="%2$s">%3$s</nav>',
								'walker' => new Sysui_Notifications_Area_Nav_Walker(),
							) ); ?>
							<div id="sysui-notifications-clock" class="sysui-clock"></div>
						</div>
					</div>
				</div>
			</div>
		</header>
		<nav class="sysui-panel-outer" id="start-menu">
			<div class="sysui-panel-inner">
				<div id="start-menu-wrapper">
					<div id="sysui-os-identifier">
						<span id="sysui-os-identifier-rotated-content-outer">
							<span id="sysui-os-identifier-rotated-content-inner"><?php bloginfo( 'description', 'display' ) ?></span>
						</span>
					</div>
					<?php wp_nav_menu( array(
						'theme_location' => 'start_menu',
						'container' => false,
						'menu_class' => 'sysui-start-menu',
						'items_wrap' => '<nav id="sysui-start-menu" class="%2$s">%3$s</nav>',
						'walker' => new Start_Menu_Nav_Walker(),
						'fallback_cb' => array( 'Start_Menu_Nav_Walker', 'fallback' ),
					) ); ?>
				</div>
			</div>
		</nav>