#!/bin/bash

# 2015 Paris 1 Panthéon-Sorbonne

# Script to update WAYF's metadata
# Usage : ./update.sh <metadata_to_use>
# Exemple : ./update.sh renater

GEOWAYFDIR=$(dirname $0)
PATHtoWAYF=$GEOWAYFDIR/..

if [ $# -ne 1 ]
	then
	echo "Error"
	echo "Usage : Parameter : Federation to use (renater, renater-test, edugain (edugain + renater), edugain-test (edugain + renater-test))"
	exit 1
fi

case $1 in
    "renater")
	url="https://federation.renater.fr/renater/idps-renater-metadata.xml";;
    "renater-test")
	url="https://federation.renater.fr/test/renater-test-metadata.xml";;
    "edugain")
	url="https://federation.renater.fr/edugain/idps-edugain+renater+sac-metadata.xml";;
    "edugain-test")
	url="https://federation.renater.fr/edugain/idps-edugain-metadata.xml https://federation.renater.fr/test/renater-test-metadata.xml";;
    *)
	echo "Error"
        echo "Unknown federation, please update this script"
        exit 1;;
esac

# Check if a temp directory exists
if [ ! -d $PATHtoWAYF/tmp ]
	then
	mkdir $PATHtoWAYF/tmp
fi

# Download metadata
echo "Downloading metadata..."
count=0
for link in $url; do
    wget --quiet --no-check-certificate $link -O $PATHtoWAYF/tmp/$count.xml
    count=$(($count + 1))
    sleep 3;
done

if [ $count -gt 2 ]; then
    echo "Error : Can't merge more than two XML files."
    echo "Exiting"
    rm $PATHtoWAYF/tmp/*.xml
    exit 1
elif [ $count -gt 1 ]; then
    echo "Merging XML files with xmlcombine.py"
    i=0
    while [ $i -lt $count ]; do
       args="$args$PATHtoWAYF/tmp/$i.xml "
       i=$(($i + 1))
    done
    python $GEOWAYFDIR/xmlcombine.py $args > $PATHtoWAYF/tmp/metadata.xml
    rm $args
else
    mv $PATHtoWAYF/tmp/0.xml $PATHtoWAYF/tmp/metadata.xml
fi

echo "Checking if XML file is well-formed"
xmllint --noout $PATHtoWAYF/tmp/metadata.xml
if [ $? -ne 0 ]
	then
	echo "Error : XML file is corrupted. Exiting."
	rm $PATHtoWAYF/tmp/metadata.xml
	exit 1
fi

# Update geolocation hints
echo "Updating discojuice geolocation hints..."
$GEOWAYFDIR/discojuice/update-discojuice.sh > /dev/null

# Refresh WAYF's discofeed
echo "Downloading discofeeds..."
php $GEOWAYFDIR/discofeed/get-discofeed-from-metadata.php $GEOWAYFDIR/tmp/metadata.xml > /dev/null

# Update wayf's metadata
echo "Updating WAYF's metadata..."
php $PATHtoWAYF/readMetadata.php > /dev/null

# Update icones and sprite sheet
echo "Updating icones and sprite sheet..."
$GEOWAYFDIR/favicon-fetcher/update-sprite-sheet.sh

exit 0
