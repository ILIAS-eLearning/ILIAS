#! /bin/bash 

cp ~/Documents/git/yui2/sandbox/reset/README	~/Documents/git/yui2/src/reset/README
cp ~/Documents/git/yui2/sandbox/reset/README	~/Documents/git/yui2/build/reset/README

cp ~/Documents/git/yui2/sandbox/reset/reset.css ~/Documents/git/yui2/src/reset/css/reset.css

java -jar ~/Documents/git/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type css --line-break 8000 ~/Documents/git/yui2/src/reset/css/reset.css -o ~/Documents/git/yui2/build/reset/reset-min.css


echo "Finished."


