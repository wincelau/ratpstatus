#!/bin/bash

todaydate=$(date +%Y%m%d --date="-3 hours")
currentdate=$todaydate

if test $1; then
    currentdate=$1
fi

mkdir static/$currentdate 2> /dev/null

php index.php $currentdate metros > static/$currentdate/metros.html.tmp
php index.php $currentdate trains > static/$currentdate/trains.html.tmp
php index.php $currentdate tramways > static/$currentdate/tramways.html.tmp
php json.php $currentdate > static/$currentdate.json.tmp
php csv.php $currentdate > static/$currentdate.csv.tmp

mv static/$currentdate.json{.tmp,}
mv static/$currentdate/metros.html{.tmp,}
mv static/$currentdate/trains.html{.tmp,}
mv static/$currentdate/tramways.html{.tmp,}
mv static/$currentdate.csv{.tmp,}

ln -fs $todaydate/metros.html static/metros.html
ln -fs $todaydate/tramways.html static/tramways.html
ln -fs $todaydate/trains.html static/trains.html
ln -fs $todaydate/list.html static/list.html
