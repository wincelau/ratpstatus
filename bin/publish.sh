#!/bin/bash

currentdate=$(date +%Y%m%d --date="-3 hours")
git pull

mkdir static/$currentdate 2> /dev/null

php index.php $currentdate metros > static/$currentdate/metros.html
php index.php $currentdate trains > static/$currentdate/trains.html
php index.php $currentdate tramways > static/$currentdate/tramways.html

ln -fs $currentdate/metros.html static/index.html

git add static/index.html
git add static/$currentdate

git commit static/index.html static/$currentdate datas/json -m "Mise à jour des données"
git push
