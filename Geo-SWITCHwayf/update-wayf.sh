#!/bin/bash
# To configure CRON : 
# $ crontab -e
# Add this line at the end of the file to execute the script every day at 4 am
# 0 4 * * * /path-to-wayf/update-sprite-sheet.sh

set -e

# Run this script in a CRON to update Geo-SWITCHwayf (XML files, geolocation hints and icones)

if [ $# -ne 1 ]
	then
	echo "Error"
	echo "Usage : Parameter : path to WAYF directory"
	exit 1
fi

# Config
PATHtoWAYF=$1
renaterXML=https://federation.renater.fr/renater/renater-metadata.xml
IDPSrenaterXML=https://federation.renater.fr/renater/idps-renater-metadata.xml


# Update geolocation hints
$PATHtoWAYF/Geo-SWITCHwayf/discojuice/update.sh

if [ -e ! $PATHtoWAYF/tmp ]
	then
	mkdir $PATHtoWAYF/tmp
	echo "tmp/ directory created."
fi

# Download renater metadata in a temp directory
wget -P $PATHtoWAYF/tmp $renaterXML
wget -P $PATHtoWAYF/tmp $IDPSrenaterXML


# Update wayf data

if [ -f $PATHtoWAYF/tmp/renater-metadata.xml ]
	then
		mv $PATHtoWAYF/tmp/renater-metadata.xml $PATHtoWAYF/renater-metadata.xml
		php $PATHtoWAYF/readMetadata.php
		echo "Wayf data has been successfully updated."
	else
		echo "renater-metadata.xml not found" 
fi

# Update icones ans sprite sheet

if [ -f $PATHtoWAYF/tmp/idps-renater-metadata.xml ]
	then
		mv $PATHtoWAYF/tmp/idps-renater-metadata.xml $PATHtoWAYF/Geo-SWITCHwayf/favicon-fetcher/idps-renater-metadata.xml
		$PATHtoWAYF/Geo-SWITCHwayf/update-sprite-sheet.sh $PATHtoWAYF/Geo-SWITCHwayf
		echo "Icones and sprite sheet have been successfully updated."
	else 
		echo "idps-renater-metadata.xml not found" 
fi

exit 0
