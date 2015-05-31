#!/bin/bash

startdate=$(date +%s)
zipname=stadtratmeta_$(date +"%d_%m_%Y").zip

#php scrape.php --diff=10 -> diff mit limit, scant nur die ersten 10 Dokumente
php scrape.php --diff

php pdfmetascapeinfo.php --sincedate=$startdate
php scrapeinfo2csv.php

echo "\ncreate: $zipname"
rm stadtratmeta*.zip
zip -q $zipname downloads/*.scrapeinfo allfilesmeta.csv

## kopieren in den webordner
# cp $zipname ~/html/stadtratmeta
