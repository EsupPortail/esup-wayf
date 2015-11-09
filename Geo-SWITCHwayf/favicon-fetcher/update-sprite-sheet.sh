#!/bin/bash

# Shell script to update the icones' sprite sheet and the sprite-sheet-array.js file.
# Imagemagick needs to be installed

if [ $# != 1 ]
	then
	echo "Error : This script needs the federation metadata file."
	echo "Usage : update-sprite-sheet.sh path/metadata.xml"
	exit 1
fi

METADATA_FILE=$1
DOWNLOADDIR=$(dirname $METADATA_FILE)/metadata-logos
FAVFETCHDIR=$(dirname $0)
PATHTOWAYF=$(readlink -f $FAVFETCHDIR/../..)
JSDIR=$PATHTOWAYF/Geo-SWITCHwayf/js
IMAGESDIR=$PATHTOWAYF/Geo-SWITCHwayf/images

php $FAVFETCHDIR/get-all-favicons-from-metadata-file.php $METADATA_FILE 1> /dev/null

allFavicons=`ls $DOWNLOADDIR/*.ico`

# Create the sprite sheet
montage $allFavicons -tile x1 -geometry 16x16+0+0 $IMAGESDIR/sprite_sheet.png

# Add a white icone for the federations without logo at the beginning of the sprite sheet
convert $IMAGESDIR/sprite_sheet.png -splice 16x0 $IMAGESDIR/sprite_sheet.png

# Add transparency color
convert $IMAGESDIR/sprite_sheet.png -fuzz 10% -transparent white $IMAGESDIR/sprite_sheet.png

# To create sprite_sheet_array.js
echo -n "var logo_to_x = {" > $JSDIR/sprite_sheet_array.js

position=0
ls $DOWNLOADDIR | grep '.ico' | while read line;
do
	VAL=`echo "$line" | cut -d'.' -f 1,2`
	insertion="\"$VAL\" : $position,"
	echo $insertion >> $JSDIR/sprite_sheet_array.js
	position=$((position + 1))
done

sed -i '$ s/.$//' $JSDIR/sprite_sheet_array.js

echo -n "};" >> $JSDIR/sprite_sheet_array.js

exit 0