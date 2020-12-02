#!/usr/bin/env bash
source .env
rm -rf backup
mkdir backup
cp -rp moodledata/ ./backup
mysqldump -u $DB_USER -h $DB_HOST --password=$DB_PASS --all-databases --skip-comments --quick --skip-lock-tables --skip-triggers --compact > ./backup/database.sql

filename=$(date +"%Y%m%d_%H%M%S")
filename="$filename-backup.tgz"

# note there is a lifecycle rule on this bucket to delete files after 10 days
tar czf $filename backup
aws s3 cp $filename s3://lms-dumps.scholu.com/

rm -rf backup
rm -rf $filename