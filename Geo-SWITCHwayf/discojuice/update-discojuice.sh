#!/bin/bash

MYDIR=$(dirname $(readlink -f $0))
DESTDIR=$1

# Add urls to retrieve geolocation data
GEOURLS="https://eduspot.renater.fr/discojuice/feed/renater https://eduspot.renater.fr/discojuice/feed/edugain"

[ -d "$DESTDIR" ] || { echo "invalid DESTDIR $DESTDIR"; exit 1; }
cd $DESTDIR

# copy local files
cp $MYDIR/*.json $DESTDIR

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
echo " > "$$ > $LOCK


l=0
for url in $GEOURLS
do
        wget -t 3 -T60 -q --no-check-certificate -O $l-`basename $url`.json.tmp $url
	let "l++"
        sleep 5;
done

for i in *.json.tmp; do
    mv -f $i `echo $i | sed 's!\.tmp$!!'`
done

exit 0

