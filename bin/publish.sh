#!/bin/bash

todaydate=$(date +%Y%m%d --date="-3 hours")
currentdate=$todaydate

if test $1; then
    currentdate=$1
fi

if test -f /tmp/publish.lock && test "$(stat -c %Y /tmp/publish.lock)" -lt "$(($(date +%s) - 90))"; then
    rm /tmp/publish.lock
fi;

if test -f /tmp/publish.lock; then
    echo "publish is locked"
    sleep 1;
    bash bin/publish.sh $currentdate;
    exit;
fi

touch /tmp/publish.lock

git sparse-checkout set --no-cone '/*' '!/datas/json/' "/datas/json/$currentdate"

mkdir static/$currentdate 2> /dev/null

USECACHE=1 RESETCACHE=1 php index.php $currentdate metros > static/$currentdate/metros.html.tmp
USECACHE=1 php index.php $currentdate trains > static/$currentdate/trains.html.tmp
USECACHE=1 php index.php $currentdate tramways > static/$currentdate/tramways.html.tmp
USECACHE=1 php csvincidents.php $currentdate > static/$currentdate/incidents.csv.tmp
USECACHE=1 php csvtimeline.php $currentdate > static/$currentdate/timeline.csv.tmp

mv static/$currentdate/metros.html{.tmp,}
mv static/$currentdate/trains.html{.tmp,}
mv static/$currentdate/tramways.html{.tmp,}
mv static/$currentdate/incidents.csv{.tmp,}
mv static/$currentdate/timeline.csv{.tmp,}

ln -fs $todaydate/metros.html static/metros.html
ln -fs $todaydate/tramways.html static/tramways.html
ln -fs $todaydate/trains.html static/trains.html

rm /tmp/publish.lock
