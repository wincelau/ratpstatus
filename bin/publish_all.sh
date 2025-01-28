#!/bin/bash

find static/[0-9]* -type d | cut -d "/" -f 2 | sort -r | while read file; do bash bin/publish.sh $file 1; done

mkdir data/export 2> /dev/null

cat static/*/incidents.csv | head -n 1 > data/export/historique_incidents.csv
cat static/20*/incidents.csv | grep -v "^date" >> data/export/historique_incidents.csv

cat static/*/timeline.csv | head -n 1 > data/export/historique_statuts.csv
cat static/20*/timeline.csv | grep -v "^date" >> data/export/historique_statuts.csv
