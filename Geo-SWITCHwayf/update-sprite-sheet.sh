#!/bin/bash

# Shell script to update the icones' sprite sheet and the sprite-sheet-array.js file.
# This script should be placed at the program root
# It has to be executed with a cron rule every day

# Imagemagick needs to be installed

# To configure CRON : 
# $ crontab -e
# Add this line at the end of the file to execute the script every day at 4 am
# 0 4 * * * /path-to-wayf/update-sprite-sheet.sh

# Favicon-Fetcher directory
FAVFETCHDIR="Favicon-Fetcher"

# Javascript directory
JSDIR="js"

# Images directory
IMAGESDIR="images"

# Execute Favicon-Fetcher
php $FAVFETCHDIR/get-all-favicons-from-metadata-file.php $FAVFETCHDIR/idps-renater-metadata.xml

allFavicons=`ls $FAVFETCHDIR/idps-renater-metadata-logos/*.ico`

# Create the sprite sheet
montage $allFavicons -tile x1 -geometry 16x16+0+0 $IMAGESDIR/sprite_sheet.png

# Add a white icone for the federations without logo at the beginning of the sprite sheet
convert $IMAGESDIR/sprite_sheet.png -splice 16x0 $IMAGESDIR/sprite_sheet.png

# Add transparency color
convert $IMAGESDIR/sprite_sheet.png -fuzz 10% -transparent white $IMAGESDIR/sprite_sheet.png

# To create sprite_sheet_array.js
echo -n "var logos = [" > $JSDIR/temp.js

ls $FAVFETCHDIR/idps-renater-metadata-logos/ | grep '.ico' | while read line;
do
	VAL=`echo "$line" | cut -d'.' -f 1,2`
	guillemet="\""
	virgule=","
	insertion=$guillemet$VAL$guillemet$virgule
	echo $insertion >> $JSDIR/temp.js
done

# Delete last coma
sed '$ s/.$//' $JSDIR/temp.js > $JSDIR/sprite_sheet_array.js
rm $JSDIR/temp.js

echo -n "];" >> $JSDIR/sprite_sheet_array.js