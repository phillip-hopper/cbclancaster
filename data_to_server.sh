#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
stamp="$( date +"%Y-%m-%d_%T" )"
dumpDir="${thisDir}/sql-backups/"
dumpFile="${dumpDir}${stamp}_to_aws.sql"
localDb="cbclancaster"
remoteDb="cbclancaster"

echo "Renaming old dump files"

if [[ -f ${dumpFile} ]]
then
	mv --backup=numbered "${dumpFile}" "${dumpFile}.bak"
fi

echo "Creating ${dumpDir}"

mkdir -p "${dumpDir}"

echo "Backing up ${localDb}"

mysqldump --defaults-file=~/.mysql/my.local.conf --default-character-set=utf8 --add-drop-database --routines --single-transaction --quick --result-file="${dumpFile}" --databases "${localDb}"

echo "Restoring to ${remoteDb}"

mysql --defaults-file=~/.mysql/my.digital.conf --default-character-set=utf8 --database=${remoteDb} < "${dumpFile}"

echo "Finished"
