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
wget --quiet --no-check-certificate $url -O $PATHtoWAYF/tmp/metadata.xml
sleep 3;

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
echo "Updating discofeed..."
php $GEOWAYFDIR/discofeed/get-discofeed-from-array.php $PATHtoWAYF/discofeed.metadata.php > /dev/null

# Update wayf's metadata
echo "Updating WAYF's metadata..."
php $PATHtoWAYF/readMetadata.php > /dev/null

# Update icones and sprite sheet
echo "Updating icones and sprite sheet..."
$GEOWAYFDIR/favicon-fetcher/update-sprite-sheet.sh

exit 0
