#!/bin/sh

# Build the JavaScript, images and CSS

ant all

cp build_tmp/carouselariaplugin.js ../../../yui2-docs/templates/examples/carousel/assets/
cp build_tmp/carouselariaplugin-min.js ../../../yui2-docs/templates/examples/carousel/assets/
