#!/bin/bash

todaydate=$(date +%Y%m%d --date="-3 hours")
currentdate=$todaydate

if test $1; then
    currentdate=$1
fi

currentmonth=$(echo -n $currentdate | cut -c -6)

mkdir static/$currentdate 2> /dev/null
mkdir static/$currentmonth 2> /dev/null

USECACHE=1 RESETCACHE=1 php index.php $currentdate metros > static/$currentdate/metros.html.tmp
USECACHE=1 php index.php $currentdate trains > static/$currentdate/trains.html.tmp
USECACHE=1 php index.php $currentdate tramways > static/$currentdate/tramways.html.tmp
USECACHE=1 php json.php $currentdate > static/$currentdate.json.tmp
USECACHE=1 php csvincidents.php $currentdate > static/$currentdate/incidents.csv.tmp
USECACHE=1 php csvtimeline.php $currentdate > static/$currentdate/timeline.csv.tmp

php month.php $currentmonth metros > static/$currentmonth/metros.html.tmp
php month.php $currentmonth trains > static/$currentmonth/trains.html.tmp
php month.php $currentmonth tramways > static/$currentmonth/tramways.html.tmp

mv static/$currentdate.json{.tmp,}
mv static/$currentdate/metros.html{.tmp,}
mv static/$currentdate/trains.html{.tmp,}
mv static/$currentdate/tramways.html{.tmp,}
mv static/$currentdate/incidents.csv{.tmp,}
mv static/$currentdate/timeline.csv{.tmp,}
mv static/$currentmonth/metros.html{.tmp,}
mv static/$currentmonth/trains.html{.tmp,}
mv static/$currentmonth/tramways.html{.tmp,}

ln -fs $todaydate/metros.html static/metros.html
ln -fs $todaydate/tramways.html static/tramways.html
ln -fs $todaydate/trains.html static/trains.html
