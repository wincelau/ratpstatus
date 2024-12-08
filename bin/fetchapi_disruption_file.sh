#!/bin/bash
APIKEY=$1

todaydate=$(date +%Y%m%d --date="-3 hours")
filename=$(date +%Y%m%d%H%M%S)_disruptions
filebrut=datas/jsonbrut/$todaydate/$filename.json
fileoptimized=datas/json/$todaydate/"$filename".optimized.json
filedisruptionids=datas/disruptions_ids/"$todaydate"_disruptions_ids.csv
mkdir -p datas/jsonbrut/$todaydate
mkdir -p datas/json/$todaydate
mkdir -p datas/disruptions_ids

curl -H "apiKey: $APIKEY" https://prim.iledefrance-mobilites.fr/marketplace/disruptions_bulk/disruptions/v2 > $filebrut

echo "Metro LocalTrain RapidTransit Tramway" | tr " " "\n" | while read mode; do
    curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3A$mode/line_reports?count=100&disable_geojson=true" -H 'accept: application/json' | tr "," "\n" | grep -B 1 disruption_id | tr -d "\n" | tr "{" "\n" | cut -d '"' -f 4,8 | sed 's/"/,/' | grep "," | sort | uniq >> $filebrut.ids.csv;
done;

php bin/optimize_disruption_file.php $filebrut $filebrut.ids.csv > $fileoptimized

gzip datas/jsonbrut/$todaydate/*.json &
gzip datas/jsonbrut/$todaydate/*.csv; zcat datas/jsonbrut/$todaydate/*.csv.gz | sort | uniq > $filedisruptionids.tmp && mv $filedisruptionids.tmp $filedisruptionids &
