#!/bin/sh

# Build the JavaScript, images and CSS

ant all

cp build_tmp/tabviewariaplugin.js ../../../yui2-docs/templates/examples/tabview/assets/
cp build_tmp/tabviewariaplugin-min.js ../../../yui2-docs/templates/examples/tabview/assets/
