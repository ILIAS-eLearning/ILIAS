#!/bin/bash

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Deploying ILIAS"

./CI/Packaging/upload-asset.sh github_api_token=$ILIAS_GITHUB_TOKEN owner=ILIAS-eLearning repo=ILIAS tag=LATEST filename="./CI/Packaging/package/ilias.tar.gz"

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Finished deploying ILIAS"
