APIKEY=$1

mkdir datas/jsontrafficreports

file=datas/jsontrafficreports/$(date +%Y%m%d%H%M%S)_traffic_reports.json

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/traffic_reports?count=100" -H 'accept: application/json' > $file

gzip datas/jsontrafficreports/*.json &
