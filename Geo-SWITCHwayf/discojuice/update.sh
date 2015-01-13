#!/bin/bash

MYDIR=$(dirname $(readlink -f $0))

# Add urls to retrieve geolocation data
GEOURLS=""

cd $MYDIR

# lock to avoid concurrent updates
LOCK="update-map-in-progress.lock"
if [ -r $LOCK ]
then
	PID=`cat $LOCK`
	ps -p $PID >/dev/null && exit
fi
unlock() {
        rm -f $LOCK
        exit
}
trap unlock INT TERM EXIT
echo $$ > $LOCK


for url in $GEOURLS
do
        wget -q --no-check-certificate -O `basename $url`.json.tmp $url
        sleep 10;
done

for i in *.json.tmp; do
    mv -f $i `echo $i | sed 's!\.tmp$!!'`
done

exit 0

