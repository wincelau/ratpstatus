APIKEY=$1

todaydate=$(date +%Y%m%d --date="-3 hours")

mkdir -p datas/jsonlinereports/$todaydate

file=datas/jsonlinereports/$todaydate/$(date +%Y%m%d%H%M%S)_line_reports

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3AMetro/line_reports?count=100" -H 'accept: application/json' > $file.metro.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ALocalTrain/line_reports?count=100" -H 'accept: application/json' > $file.transilien.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ARapidTransit/line_reports?count=100" -H 'accept: application/json' > $file.rer.json
curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/line_reports/physical_modes%2Fphysical_mode%3ATramway/line_reports?count=100" -H 'accept: application/json' > $file.tramway.json

gzip datas/jsonlinereports/$todaydate/*.json &
