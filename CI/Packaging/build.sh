#!/bin/bash

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Building ILIAS"

# copy data
rsync -av --progress -q ./ "./CI/Packaging/package" --exclude .git --exclude CI --exclude Customizing --exclude packaging --exclude 'tests*' --exclude 'test*'

# clean
rm ./CI/Packaging/package/.travis.yml
rm ./CI/Packaging/package/.gitignore
rm ./CI/Packaging/package/.gitkeep

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Finished building ILIAS"
