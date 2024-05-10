APIKEY=$1

mkdir datas/jsonlinereports
file=datas/jsonlinereports/$(date +%Y%m%d%H%M%S)_line_reports

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3AMetro/line_reports?count=100" -H 'accept: application/json' | jq > $file.metro.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ALocalTrain/line_reports?count=100" -H 'accept: application/json' | jq > $file.transilien.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ARapidTransit/line_reports?count=100" -H 'accept: application/json' | jq > $file.rer.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ATramway/line_reports?count=100" -H 'accept: application/json' | jq > $file.tramway.json
