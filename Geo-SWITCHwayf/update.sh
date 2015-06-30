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
	url="https://federation.renater.fr/edugain/idps-edugain-metadata.xml https://federation.renater.fr/renater/idps-renater-metadata.xml";;
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
fileCount=0
for xmlFile in $url
do
	echo "Downloading $xmlFile"
	fileCount=$((fileCount+1))
	wget --quiet --no-check-certificate $xmlFile -O $PATHtoWAYF/tmp/$fileCount.xml
	cat $PATHtoWAYF/tmp/$fileCount.xml >> $PATHtoWAYF/tmp/tempXML.xml
	echo -e "\n" >> $PATHtoWAYF/tmp/tempXML.xml
	rm $PATHtoWAYF/tmp/$fileCount.xml
	sleep 3;
done

if [ $fileCount -gt 1 ]
	then
	sed -i 's/Signature>/Signature>\n/g' $PATHtoWAYF/tmp/tempXML.xml
	sed -i '/<\/md:EntitiesDescriptor>/,/<\/ds:Signature>/d' $PATHtoWAYF/tmp/tempXML.xml
	sed -i 's/<\/EntitiesDescriptor>//g' $PATHtoWAYF/tmp/tempXML.xml
	echo "</md:EntitiesDescriptor>" >> $PATHtoWAYF/tmp/tempXML.xml
	sleep 2;
fi

echo "Checking if XML file is well-formed"
xmllint --noout $PATHtoWAYF/tmp/tempXML.xml
if [ $? -ne 0 ]
	then
	echo "Error : XML file is corrupted. Exiting."
	rm $PATHtoWAYF/tmp/tempXML.xml
	exit 1
else
	mv $PATHtoWAYF/tmp/tempXML.xml $PATHtoWAYF/tmp/metadata.xml
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
