APIKEY=$1

file=datas/jsonlines/$(date +%Y%m%d%H%M%S)_lines.json

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/lines?count=100&forbidden_id%5B%5D=physical_mode%3ABus&disable_geojson=true&disable_disruption=true" -H 'accept: application/json' > $file
