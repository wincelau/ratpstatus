#!/bin/bash
APIKEY=$1

filename=$(date +%Y%m%d%H%M%S)_disruptions
filebrut=datas/jsonbrut/$filename.json
fileoptimized=datas/json/$filename.optimized.json
curl -H "apiKey: $APIKEY" https://prim.iledefrance-mobilites.fr/marketplace/disruptions_bulk/disruptions/v2 > $filebrut
php bin/optimize_disruption_file.php $filebrut > $fileoptimized
git pull
git add $fileoptimized
git commit $fileoptimized -m "New disruption file"
git push
gzip datas/jsonbrut/*.json &
