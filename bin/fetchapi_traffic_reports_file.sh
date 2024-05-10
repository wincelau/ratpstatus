APIKEY=$1

file=datas/jsontrafficreports/$(date +%Y%m%d%H%M%S)_lines.json

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/traffic_reports?count=100" -H 'accept: application/json' | jq > $file

# git pull
# git add $file
# git commit $file -m "Récupération du fichier lines.json pour les horaires"
# git push
