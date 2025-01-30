#!/bin/bash

find static/[0-9]* -type d | grep -E "static/[0-9]{8}" | cut -d "/" -f 2 | sort -r | while read day; do bash bin/publish.sh $day 1; done

mkdir datas/export 2> /dev/null

cat static/20*/incidents.csv | head -n 1 > datas/export/historique_incidents.csv
cat static/20*/incidents.csv | grep -v "^date" >> datas/export/historique_incidents.csv

cat static/20*/timeline.csv | head -n 1 > datas/export/historique_statuts.csv
cat static/20*/timeline.csv | grep -v "^date" >> data/exports/historique_statuts.csv

find static/[0-9]* -type d | grep -E "static/[0-9]{8}" | cut -d "/" -f 2 | cut -c -6 | sort | uniq | sort -r | while read month; do
    mkdir static/$month 2> /dev/null
    php month.php $month metros > static/$month/metros.html.tmp
    php month.php $month trains > static/$month/trains.html.tmp
    php month.php $month tramways > static/$month/tramways.html.tmp
    mv static/$month/metros.html{.tmp,}
    mv static/$month/trains.html{.tmp,}
    mv static/$month/tramways.html{.tmp,}
done
