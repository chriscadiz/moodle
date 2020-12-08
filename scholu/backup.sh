#!/usr/bin/env bash
source .env
rm -rf backup
mkdir backup
cp -rp moodledata/ ./backup


ignoreDbs=('information_schema' 'alpha_reporting' 'delta_reporting' 'gamma_reporting' 'beta_reporting' 'innodb' 'mysql' 'performance_schema' 'reporting' 'sncl' 'sys' 'tmp' )

mysql -u $DB_USER -h $DB_HOST --password=$DB_PASS -N -e "SHOW DATABASES;" | while IFS= read -r database
do
    backup=true;
	for i in "${ignoreDbs[@]}"; do
	  if [[ "$loop" = "$i" ]]; then
		backup=false;
	  fi
	done
	if $backup; then
		mysqldump -u $DB_USER -h $DB_HOST --password=$DB_PASS $database --skip-comments --single-transaction --quick --skip-lock-tables --skip-triggers --compact --set-gtid-purged=OFF > ./backup/$database.sql
	fi
done

filename=$(date +"%Y%m%d_%H%M%S")
filename="$filename-backup.tgz"

# note there is a lifecycle rule on this bucket to delete files after 10 days
tar czf $filename backup
aws s3 cp $filename s3://lms-dumps.scholu.com/

rm -rf backup
rm -rf $filename