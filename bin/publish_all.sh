#!/bin/bash

git ls-tree --name-only main datas/json/ | cut -d "/" -f 3 | sort -r | while read day; do bash bin/publish.sh $day; done

mkdir datas/export 2> /dev/null

cat static/20*/incidents.csv | head -n 1 > datas/export/historique_incidents.csv
cat static/20*/incidents.csv | grep -v "^date" >> datas/export/historique_incidents.csv

cat static/20*/timeline.csv | head -n 1 > datas/export/historique_statuts.csv
cat static/20*/timeline.csv | grep -v "^date" >> datas/export/historique_statuts.csv

find static/[0-9]* -type d | grep -E "static/[0-9]{8}" | cut -d "/" -f 2 | cut -c -6 | sort | uniq | sort -r | while read month; do
    mkdir static/$month 2> /dev/null
    php month.php $month metros > static/$month/metros.html.tmp
    php month.php $month trains > static/$month/trains.html.tmp
    php month.php $month tramways > static/$month/tramways.html.tmp
    mv static/$month/metros.html{.tmp,}
    mv static/$month/trains.html{.tmp,}
    mv static/$month/tramways.html{.tmp,}
done

mkdir static/12derniersmois 2> /dev/null
php year.php 12derniersmois metros > static/12derniersmois/metros.html.tmp
php year.php 12derniersmois trains > static/12derniersmois/trains.html.tmp
php year.php 12derniersmois tramways > static/12derniersmois/tramways.html.tmp
mv static/12derniersmois/metros.html{.tmp,}
mv static/12derniersmois/trains.html{.tmp,}
mv static/12derniersmois/tramways.html{.tmp,}

find static/[0-9]* -type d | grep -E "static/[0-9]{8}" | cut -d "/" -f 2 | cut -c -4 | sort | uniq | sort -r | while read year; do
    mkdir static/$year 2> /dev/null
    php year.php $year metros > static/$year/metros.html.tmp
    php year.php $year trains > static/$year/trains.html.tmp
    php year.php $year tramways > static/$year/tramways.html.tmp
    mv static/$year/metros.html{.tmp,}
    mv static/$year/trains.html{.tmp,}
    mv static/$year/tramways.html{.tmp,}
done
