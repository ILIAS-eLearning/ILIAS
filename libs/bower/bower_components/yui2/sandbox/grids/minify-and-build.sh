#! /bin/bash 

cp ~/Documents/git/yui2/sandbox/grids/README	~/Documents/git/yui2/src/grids/README
cp ~/Documents/git/yui2/sandbox/grids/README	~/Documents/git/yui2/build/grids/README

cp ~/Documents/git/yui2/sandbox/grids/grids.css ~/Documents/git/yui2/src/grids/css/grids.css

java -jar ~/Documents/git/yuicompressor-2.4.2/build/yuicompressor-2.4.2.jar --type css --line-break 8000 ~/Documents/git/yui2/src/grids/css/grids.css -o ~/Documents/git/yui2/build/grids/grids-min.css


echo "Finished."


