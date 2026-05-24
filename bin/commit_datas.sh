#!/bin/bash

if test $1; then
    commitdate=$1
else
    commitdate=$(date +%Y%m%d --date="-1 day")
fi

git sparse-checkout set --no-cone '/*' '!/datas/json/' "/datas/json/$commitdate"

git add datas/disruptions_ids
git add datas/jsonlines
git add datas/json/$commitdate
git commit datas -m "Données de la journée $commitdate"
git pull --commit
git push
