#!/bin/bash

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Packing ILIAS"

DIR=$(pwd)

cd "./CI/Packaging/package/"
tar -zcf "ilias.tar.gz" *
cd $DIR

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Finished packing ILIAS"
