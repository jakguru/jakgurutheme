# Jak Guru Theme

## Dev Notes

This project uses Gulp for managing CSS and JS requirements. It is setup to:

* Automatically convert `.sass` and `.scss` files found under `./assets/scss/` into minified `css` files
* Automatically minify `.js` files found under `./assets/sjs` into minified `js` files

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