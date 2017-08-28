#!/bin/sh

# Build the JavaScript, images and CSS

ant all

cp build_tmp/buttonariaplugin.js ../../../yui2-docs/templates/examples/button/assets/
cp build_tmp/buttonariaplugin-min.js ../../../yui2-docs/templates/examples/button/assets/