#!/bin/bash -e

# 2015 Paris 1 Panthéon-Sorbonne

# Script to update WAYF's metadata
# Usage : ./update.sh <metadata_to_use>
# Exemple : ./update.sh renater
# Should be executed into www/ were user has permission, the script will generate files into the folder

echo "============  $(date +"%d-%m-%Y %T") ============="
GEOWAYFDIR=$(dirname $0)
PATHtoWAYF=$GEOWAYFDIR/..
TMPDIR=$(mktemp --directory --suffix .wayf-update)


if [ $# -ne 1 ]
	then
	echo "Error"
	echo "Usage : Parameter : Federation to use (renater, renater-test, edugain (edugain + renater), edugain-test (edugain + renater-test))"
	exit 1
fi

case $1 in
    "renater")
	url="https://metadata.federation.renater.fr/fer/all.xml.gz";;
    "renater-test")
	url="https://metadata.federation.renater.fr/test/all.xml.gz";;
    "edugain")
	url="https://metadata.federation.renater.fr/edugain/all.xml.gz";;
    "renater-edugain")
	url="https://metadata.federation.renater.fr/edugain/all.xml.gz https://metadata.federation.renater.fr/fer/all.xml.gz";;
    "renater-test-and-edugain")
	url="https://metadata.federation.renater.fr/test/all.xml.gz https://metadata.federation.renater.fr/edugain/all.xml.gz";;
    *)
	echo "Error"
        echo "Unknown federation, please update this script"
        exit 1;;
esac


# Download metadata
echo "Downloading metadata $1..."
count=0
for link in $url; do
    if [[ $link == *gz ]]; then
        wget --quiet --no-check-certificate $link -O - | gunzip > $TMPDIR/$count.xml
    else
        wget --quiet --no-check-certificate $link -O $TMPDIR/$count.xml
    fi
    echo " > Downloaded $link -> $TMPDIR/$count.xml"
    count=$(($count + 1))
    sleep 3;
done

if [ $count -gt 1 ]; then
    echo "Merging XML files with xmlcombine.py"
    i=0
    while [ $i -lt $count ]; do
       args="$args$TMPDIR/$i.xml "
       i=$(($i + 1))
    done
    python3 $GEOWAYFDIR/xmlcombine.py $args > $TMPDIR/metadata.xml
    rm $args
else
	 	echo "Moving $TMPDIR/0.xml -> $TMPDIR/metadata.xml"
    mv $TMPDIR/0.xml $TMPDIR/metadata.xml
fi

echo "Checking if XML file is well-formed: $TMPDIR/metadata.xml"
xmllint --noout $TMPDIR/metadata.xml
if [ $? -ne 0 ]
	then
	echo "Error : XML file is corrupted. Exiting."
	rm $TMPDIR/metadata.xml
	exit 1
fi

echo "Getting discojuice geolocation hints..."
mkdir -p $TMPDIR/discojuice
$GEOWAYFDIR/discojuice/update-discojuice.sh $TMPDIR/discojuice

echo "Updating WAYF's IDProvider.metadata.php & SProvider.metadata.php..."
php $PATHtoWAYF/readMetadata.php $TMPDIR/metadata.xml $TMPDIR/discojuice

# not needed anymore:
rm -rf $TMPDIR


echo "Updating discofeeds for SPs..."
# uses $discoFeedCacheDir/known-sp.php (contient les SPs demandé au moins une fois. Il est mis à jour dynamiquement par la fonction "addNewDiscofeedURL" dans WAYF)
# modifies: $discoFeedCacheDir/*.json
php $GEOWAYFDIR/discofeed/get-discofeed-from-array.php


exit 0
