#! /bin/bash 

cp ~/Documents/git/yui2/sandbox/base/README	~/Documents/git/yui2/src/base/README
cp ~/Documents/git/yui2/sandbox/base/README	~/Documents/git/yui2/build/base/README

cp ~/Documents/git/yui2/sandbox/base/base.css ~/Documents/git/yui2/src/base/css/base.css

java -jar ~/Documents/git/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type css --line-break 8000 ~/Documents/git/yui2/src/base/css/base.css -o ~/Documents/git/yui2/build/base/base-min.css


echo "Finished."


