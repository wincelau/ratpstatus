APIKEY=$1

todaydate=$(date +%Y%m%d --date="-3 hours")


mkdir datas/jsontrafficreports/$todaydate

file=datas/jsontrafficreports/$todaydate/$(date +%Y%m%d%H%M%S)_traffic_reports.json

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/traffic_reports?count=100" -H 'accept: application/json' > $file

gzip datas/jsontrafficreports/$todaydate/*.json &
