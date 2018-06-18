# Jak Guru Theme

This theme is a technology demonstrator for WordPress. It is designed to show just how flexible WordPress's API's actually are and what you can do with it. Some of the interesting functions included include:

* Some Basic Security & Performance Features
  * Removing WordPress Emojis
  * Removing WordPress Generator Information
* Theme Supports
  * Title Tag
  * Post Thumbnails
  * Navigation Menus
  * HTML5
  * Custom Logo
  * Custom Background
  * Automatic Feed Links
 * Adding WP Customize Controls
 * Adding Custom Fields to the WordPress Menu Editor
 * Custom Navigation Menu Walkers

## Dev Notes

This project uses Gulp for managing CSS and JS requirements. It is setup to:

* Automatically convert `.sass` and `.scss` files found under `./assets/scss/` into minified `css` files
* Automatically minify `.js` files found under `./assets/sjs` into minified `js` files

To generate the file tree listed below, use `tree -I '.sass-cache|node_modules|package-lock.json'` from the root of the project

*NOTE* This project requires `gulp-ruby-sass`, which requires the Ruby version of SASS.

### Setting Up Dev Environment

This setup assumes that you have the latest versions of nodejs and npm installed on your computer.

1. Navigate to the theme root. This tutorial references the theme root as `./`
2. From the command line, run `npm install`

### Gulp Commands

This project has the following gulp commands configured:

* `default` - Watch for changes across files and update the relevant CSS & JS files accordingly
* `watch` - Watch for changes across files and update the relevant CSS & JS files accordingly
* `watch:sass` - Watch for changes across `.sass` and `.scss` files and compile them into their minified "production" versions
* `watch:js` - Watch for changes across `.js` files and compile them into their minified "production" versions
* `compile` - Compile `.sass`, `.scss` and `.js` files into their minified "production" versions
* `compile:sass` - Compile `.sass` and `.scss` files into their minified "production" versions
* `compile:js` - Compile `.js` files into their minified "production" versions

## Included Files

```
.
├── assets
│   ├── admin
│   │   ├── admin.css
│   │   ├── admin.js
│   │   └── index.php
│   ├── css
│   │   ├── app.min.css
│   │   └── index.php
│   ├── fonts
│   │   ├── index.php
│   │   ├── MicrosoftSansSerif.woff
│   │   ├── MicrosoftSansSerif.woff2
│   │   └── micross.ttf
│   ├── images
│   │   ├── close.png
│   │   ├── controlpanel.png
│   │   ├── defaultapp.png
│   │   ├── desktop.png
│   │   ├── documents.png
│   │   ├── folder.png
│   │   ├── forbidden.png
│   │   ├── help.png
│   │   ├── index.php
│   │   ├── info.png
│   │   ├── keys.png
│   │   ├── link.png
│   │   ├── lock.png
│   │   ├── maximize.png
│   │   ├── minimize.png
│   │   ├── multiplefiles.png
│   │   ├── notepad.png
│   │   ├── openfolder.png
│   │   ├── programs.png
│   │   ├── restore.png
│   │   ├── search.png
│   │   ├── shutdown.png
│   │   ├── stop.png
│   │   ├── system.png
│   │   ├── text.png
│   │   └── video.png
│   ├── index.php
│   ├── js
│   │   ├── app.min.js
│   │   └── index.php
│   ├── scss
│   │   ├── _colors.scss
│   │   ├── _desktop.scss
│   │   ├── global.scss
│   │   ├── index.php
│   │   ├── _startmenu.scss
│   │   ├── _sysui.scss
│   │   ├── _sysuiwindow.scss
│   │   └── _taskbar.scss
│   ├── sjs
│   │   ├── bootstrap.js
│   │   ├── core.js
│   │   ├── history.js
│   │   ├── index.php
│   │   └── sysui-windows.js
│   └── sounds
│       └── index.php
├── footer.php
├── functions.php
├── gulpfile.js
├── header.php
├── index.php
├── LICENSE.md
├── package.json
├── private
│   ├── class-additional-menu-fields-utility.php
│   ├── class-custom-comment-walker.php
│   ├── class-page-parser.php
│   ├── class-theme-utils.php
│   ├── class-wp-customize-utility.php
│   ├── index.php
│   └── menu-walkers
│       ├── class-desktop-nav-walker.php
│       ├── class-quick-links-nav-walker.php
│       ├── class-start-menu-nav-walker.php
│       ├── class-sysui-notifications-area-nav-walker.php
│       └── index.php
├── README.md
└── style.css
```