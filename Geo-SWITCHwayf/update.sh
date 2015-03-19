#!/bin/bash
# To configure CRON : 
# $ crontab -e
# Add this line at the end of the file to execute the script every day at 4 am
# 0 4 * * * /path-to-wayf/update-sprite-sheet.sh

# Run this script in a CRON to update Geo-SWITCHwayf (XML files, geolocation hints and icones)

set -e

PATHtoWAYF=$1

# Geo-SWITCHwayf directory
GEOWAYFDIR=$PATHtoWAYF/Geo-SWITCHwayf

if [ $# -ne 2 ]
	then
	echo "Error"
	echo "Usage : Parameter 1 : path to WAYF directory"
	echo "Usage : Parameter 2 : Metadata to use (renater or renater-test)"
	exit 1
fi

if [ $2 == "renater" ] 
	then
	metadataFile=https://federation.renater.fr/renater/renater-metadata.xml
elif [ $2 == "renater-test" ]
	then
	metadataFile=https://federation.renater.fr/test/renater-test-metadata.xml
else
	echo "Error"
	echo "Unknown federation, please update this script"
	exit 1
fi

# Check if a temp directory exists
if [ ! -d $PATHtoWAYF/tmp ]
	then
	mkdir $PATHtoWAYF/tmp
fi

# Download renater metadata in a temp directory
wget --no-check-certificate $metadataFile -O $PATHtoWAYF/tmp/metadata.xml
sleep 5;

# Update geolocation hints
echo "Updating discojuice geolocation hints..."
$GEOWAYFDIR/discojuice/update-discojuice.sh

# Refresh WAYF's discofeed
echo "Updating discofeed..."
php $GEOWAYFDIR/discofeed/get-discofeed-from-array.php $PATHtoWAYF/discofeed.metadata.php

# Update wayf's metadata
echo "Updating WAYF's metadata..."
php $PATHtoWAYF/readMetadata.php

# Update icones and sprite sheet
echo "Updating icones and sprite sheet..."
$GEOWAYFDIR/favicon-fetcher/update-sprite-sheet.sh $GEOWAYFDIR

exit 0
