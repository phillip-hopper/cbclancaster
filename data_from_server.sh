#!/usr/bin/env bash

thisDir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
stamp="$( date +"%Y-%m-%d_%T" )"
dumpDir="${thisDir}/sql-backups/"
dumpFile="${dumpDir}${stamp}_from_aws.sql"
preDumpFile="${dumpDir}${stamp}_before_aws.sql"
localDb="cbclancaster"
remoteDb="cbclancaster"

echo "Renaming old dump files"

if [[ -f ${dumpFile} ]]
then
	mv --backup=numbered "${dumpFile}" "${dumpFile}.bak"
fi

echo "Creating ${dumpDir}"

mkdir -p "${dumpDir}"

echo "Backing up local, just in case"

mysqldump --defaults-file=~/.mysql/my.local.conf --default-character-set=utf8 --add-drop-database --routines --single-transaction --quick --result-file="${preDumpFile}" --databases "${localDb}"

echo "Backing up ${localDb}"

mysqldump --defaults-file=~/.mysql/my.digital.conf --default-character-set=utf8 --add-drop-database --routines --single-transaction --quick --result-file="${dumpFile}" --databases "${localDb}"

echo "Cleaning up ${localDb}"
# shellcheck disable=SC2016
sed -i -E 's/\/\*\!50017\sDEFINER=.+`@`(%|localhost)`\*\// /g' "${dumpFile}"
# shellcheck disable=SC2016
sed -i -E 's/\sDEFINER=.+`@`(%|localhost)`\s/ /g' "${dumpFile}"
sed -i -E 's/,?NO_AUTO_CREATE_USER//g' "${dumpFile}"

echo "Restoring to ${remoteDb}"

mysql --defaults-file=~/.mysql/my.local.conf --default-character-set=utf8 --database=${remoteDb} -e "SOURCE ${dumpFile};"

echo "Finished"
