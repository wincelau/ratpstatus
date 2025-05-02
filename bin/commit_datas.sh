    #!/bin/bash

commitdate=$(date +%Y%m%d --date="-1 day")

git sparse-checkout set --no-cone '/*' '!/datas/json/' "/datas/json/$commitdate"

git add datas
git add datas/json/$commitdate
git commit datas -m "Données de la journée $commitdate"
git pull --commit
git push
