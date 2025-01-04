#!/bin/bash

find static/[0-9]* -type d | cut -d "/" -f 2 | sort -r | while read file; do bash bin/publish.sh $file 1; done

mkdir static/export 2> /dev/null

cat static/*/incidents.csv | head -n 1 > static/export/historique_incidents.csv
cat static/20*/incidents.csv | grep -v "^date" >> static/export/historique_incidents.csv

cat static/*/timeline.csv | head -n 1 > static/export/historique_statuts.csv
cat static/20*/timeline.csv | grep -v "^date" >> static/export/historique_statuts.csv
