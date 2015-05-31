#!/bin/bash

startdate=$(date +%s)
zipname=stadtratmeta_$(date +"%d_%m_%Y").zip

php scrape.php --trynext=2000
php pdfmetascapeinfo.php --sincedate=$startdate
php scrapeinfo2csv.php


echo "create zip: $zipname"
zip -q $zipname downloads/*.scrapeinfo allfilesmeta.csv

## kopieren in den webordner
# cp $zipname /var/www/stadtratmeta
