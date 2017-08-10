#!/bin/sh

# Build the JavaScript, images and CSS

ant all

cp build_tmp/menuariaplugin.js ../../../yui2-docs/templates/examples/menu/assets/
cp build_tmp/menuariaplugin-min.js ../../../yui2-docs/templates/examples/menu/assets/