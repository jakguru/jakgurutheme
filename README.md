# Jak Guru Theme

## Dev Notes

This project uses Gulp for managing CSS and JS requirements. It is setup to:

* Automatically convert `.sass` and `.scss` files found under `./assets/scss/` into minified `css` files
* Automatically minify `.js` files found under `./assets/sjs` into minified `js` files

To generate the file tree listed below, use `tree -I '.sass-cache|node_modules'` from the root of the project

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
│   │   └── index.php
│   ├── index.php
│   ├── js
│   │   ├── app.min.js
│   │   └── index.php
│   ├── scss
│   │   ├── global.scss
│   │   └── index.php
│   ├── sjs
│   │   ├── bootstrap.js
│   │   ├── core.js
│   │   └── index.php
│   └── sounds
│       └── index.php
├── footer.php
├── functions.php
├── gulpfile.js
├── header.php
├── index.php
├── LICENSE.md
├── package.json
├── package-lock.json
├── private
│   ├── class-additional-menu-fields-utility.php
│   ├── class-theme-utils.php
│   ├── class-wp-customize-utility.php
│   ├── index.php
│   └── menu-walkers
│       ├── class-quick-links-nav-walker.php
│       └── index.php
├── README.md
└── style.css
```