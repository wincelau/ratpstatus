APIKEY=$1

file=datas/jsonlines/$(date +%Y%m%d%H%M%S)_lines.json

curl -H "apiKey: $APIKEY" "https://prim.iledefrance-mobilites.fr/marketplace/v2/navitia/lines?count=100&forbidden_id%5B%5D=physical_mode%3ABus" -H 'accept: application/json' > $file

#git pull
##git add $file
#git commit $file -m "Récupération du fichier lines.json pour les horaires"
#git push
