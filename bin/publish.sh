#!/bin/bash

currentdate=$(date +%Y%m%d --date="-3 hours")

if test $1; then
    currentdate=$1
fi

git pull

mkdir static/$currentdate 2> /dev/null

php index.php $currentdate metros > static/$currentdate/metros.html
php index.php $currentdate trains > static/$currentdate/trains.html
php index.php $currentdate tramways > static/$currentdate/tramways.html

ln -fs $currentdate/metros.html static/index.html

git add static/index.html
git add static/$currentdate

git commit static/index.html static/$currentdate -m "Publication des pages html statique pour $currentdate"
git push
