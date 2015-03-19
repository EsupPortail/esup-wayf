#!/bin/bash

# Shell script to update the icones' sprite sheet and the sprite-sheet-array.js file.
# Imagemagick needs to be installed

if [ $# -ne 1 ]
	then
	echo "Error"
	echo "Usage : Parameter : path to Geo-WAYF directory"
	exit 1
fi

PATHTOWAYF=$1
# Favicon-Fetcher directory
FAVFETCHDIR=$PATHTOWAYF/favicon-fetcher
# Javascript directory
JSDIR=$PATHTOWAYF/js
# Images directory
IMAGESDIR=$PATHTOWAYF/images

if [ ! -d $FAVFETCHDIR/idp-metadata ]
	then
	mkdir $FAVFETCHDIR/idp-metadata
fi

if [ ! -d $FAVFETCHDIR/favicons ]
	then
	mkdir $FAVFETCHDIR/favicons
fi

# Download IDP metadata
IDPURL="https://federation.renater.fr/renater/idps-renater-metadata.xml https://federation.renater.fr/edugain/idps-edugain-metadata.xml"

for idp in $IDPURL
do
 	wget --no-check-certificate $idp -O $FAVFETCHDIR/idp-metadata/`basename $idp`
done

ls $FAVFETCHDIR/idp-metadata | grep '.xml' | while read line;
do
 	# Execute Favicon-Fetcher
 	php $FAVFETCHDIR/get-all-favicons-from-metadata-file.php $FAVFETCHDIR/idp-metadata/$line
done

ls $FAVFETCHDIR/idp-metadata | while read line;
do
	if [ -d $FAVFETCHDIR/idp-metadata/$line ]
		then
		mv $FAVFETCHDIR/idp-metadata/$line/*.ico $FAVFETCHDIR/favicons
	fi
done

allFavicons=`ls $FAVFETCHDIR/favicons/*.ico`

# Create the sprite sheet
montage $allFavicons -tile x1 -geometry 16x16+0+0 $IMAGESDIR/sprite_sheet.png

# Add a white icone for the federations without logo at the beginning of the sprite sheet
convert $IMAGESDIR/sprite_sheet.png -splice 16x0 $IMAGESDIR/sprite_sheet.png

# Add transparency color
convert $IMAGESDIR/sprite_sheet.png -fuzz 10% -transparent white $IMAGESDIR/sprite_sheet.png

# To create sprite_sheet_array.js
echo -n "var logos = [" > $JSDIR/temp.js

ls $FAVFETCHDIR/favicons | grep '.ico' | while read line;
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

rm -r $FAVFETCHDIR/idp-metadata
rm -r $FAVFETCHDIR/favicons

exit 0