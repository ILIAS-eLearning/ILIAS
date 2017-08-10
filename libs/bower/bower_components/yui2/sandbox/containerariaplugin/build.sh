#!/bin/sh

# Build the JavaScript, images and CSS

ant all

cp build_tmp/containerariaplugin.js ../../../yui2-docs/templates/examples/container/assets/
cp build_tmp/containerariaplugin-min.js ../../../yui2-docs/templates/examples/container/assets/