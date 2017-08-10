#!/bin/bash

echo "Syncing files"
cp ./js/editor.js ../../src/editor/js/
wait
cp ./js/simple-editor.js ../../src/editor/js/
wait
cp ./js/toolbar-button.js ../../src/editor/js/
wait
cp ./js/toolbar.js ../../src/editor/js/
wait
echo "Done"
