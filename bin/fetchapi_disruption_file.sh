#!/bin/bash
APIKEY=$1

todaydate=$(date +%Y%m%d --date="-3 hours")
filename=$(date +%Y%m%d%H%M%S)_disruptions
filebrut=datas/jsonbrut/$todaydate/$filename.json
fileoptimized=datas/json/$todaydate/"$filename".optimized.json
mkdir -p datas/jsonbrut/$todaydate
mkdir -p datas/json/$todaydate

curl -H "apiKey: $APIKEY" https://prim.iledefrance-mobilites.fr/marketplace/disruptions_bulk/disruptions/v2 > $filebrut
php bin/optimize_disruption_file.php $filebrut > $fileoptimized
git pull --commit
git add $fileoptimized
git commit $fileoptimized -m "Récupération du dernier fichier disruption.json"
git push

gzip datas/jsonbrut/$todaydate/*.json &
