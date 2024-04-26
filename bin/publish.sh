#!/bin/bash

todaydate=$(date +%Y%m%d --date="-3 hours")
currentdate=$todaydate

if test $1; then
    currentdate=$1
fi

git pull

mkdir static/$currentdate 2> /dev/null

php index.php $currentdate metros > static/$currentdate/metros.html
php index.php $currentdate trains > static/$currentdate/trains.html
php index.php $currentdate tramways > static/$currentdate/tramways.html

ln -fs $todaydate/metros.html static/metros.html
ln -fs $todaydate/tramways.html static/tramways.html
ln -fs $todaydate/trains.html static/trains.html
ln -fs metros.html static/index.html

git add static/index.html
git add static/$currentdate

git commit static/index.html static/$currentdate -m "Publication des pages html statique pour $currentdate"
git push
