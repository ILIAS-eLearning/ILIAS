#! /bin/bash 

cp ~/Documents/git/yui2/sandbox/fonts/README	~/Documents/git/yui2/src/fonts/README
cp ~/Documents/git/yui2/sandbox/fonts/README	~/Documents/git/yui2/build/fonts/README

cp ~/Documents/git/yui2/sandbox/fonts/fonts.css ~/Documents/git/yui2/src/fonts/css/fonts.css

java -jar ~/Documents/git/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type css --line-break 8000 ~/Documents/git/yui2/src/fonts/css/fonts.css -o ~/Documents/git/yui2/build/fonts/fonts-min.css


echo "Finished."


