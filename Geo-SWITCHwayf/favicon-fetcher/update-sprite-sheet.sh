#!/bin/bash

# Shell script to update the icones' sprite sheet and the sprite-sheet-array.js file.
# Imagemagick needs to be installed

FAVFETCHDIR=$(dirname $0)
PATHTOWAYF=$FAVFETCHDIR/../..
JSDIR=$PATHTOWAYF/Geo-SWITCHwayf/js
IMAGESDIR=$PATHTOWAYF/Geo-SWITCHwayf/images

if [ -d $PATHTOWAYF/tmp/metadata-logos ]
	then
	rm -r $PATHTOWAYF/tmp/metadata-logos
fi

php $FAVFETCHDIR/get-all-favicons-from-metadata-file.php $PATHTOWAYF/tmp/metadata.xml > /dev/null

allFavicons=`ls $PATHTOWAYF/tmp/metadata-logos/*.ico`

# Create the sprite sheet
montage $allFavicons -tile x1 -geometry 16x16+0+0 $IMAGESDIR/sprite_sheet.png

# Add a white icone for the federations without logo at the beginning of the sprite sheet
convert $IMAGESDIR/sprite_sheet.png -splice 16x0 $IMAGESDIR/sprite_sheet.png

# Add transparency color
convert $IMAGESDIR/sprite_sheet.png -fuzz 10% -transparent white $IMAGESDIR/sprite_sheet.png

# To create sprite_sheet_array.js
echo -n "var logo_to_x = {" > $JSDIR/sprite_sheet_array.js

position=0
ls $PATHTOWAYF/tmp/metadata-logos | grep '.ico' | while read line;
do
	VAL=`echo "$line" | cut -d'.' -f 1,2`
	insertion="\"$VAL\" : $position,"
	echo $insertion >> $JSDIR/sprite_sheet_array.js
	position=$((position + 1))
done

sed -i '$ s/.$//' $JSDIR/sprite_sheet_array.js

echo -n "};" >> $JSDIR/sprite_sheet_array.js

exit 0