#!/bin/bash

# 2015 Paris 1 Panthéon-Sorbonne

# Script to update WAYF's metadata
# Usage : ./update.sh <metadata_to_use>
# Exemple : ./update.sh renater
# Should be executed into www/ were user has permission, the script will generate files into the folder

echo "============  $(date +"%d-%m-%Y %T") ============="
GEOWAYFDIR=$(dirname $0)
PATHtoWAYF=$GEOWAYFDIR/..
TMPDIR=$(sed -n 's/$tmpDir = "\(.*\)";/\1/p' < $PATHtoWAYF/config.php)


if [ $# -ne 1 ]
	then
	echo "Error"
	echo "Usage : Parameter : Federation to use (renater, renater-test, edugain (edugain + renater), edugain-test (edugain + renater-test))"
	exit 1
fi

case $1 in
    "renater")
	url="https://metadata.federation.renater.fr/renater/main/main-all-renater-metadata.xml";;
    "renater-test")
	url="https://metadata.federation.renater.fr/test/preview/preview-all-renater-test-metadata.xml";;
    "edugain")
	url="https://metadata.federation.renater.fr/edugain/main/main-all-edugain-metadata.xml";;
    "renater-edugain")
	url="https://metadata.federation.renater.fr/edugain/main/main-all-edugain-metadata.xml https://metadata.federation.renater.fr/renater/main/main-all-renater-metadata.xml";;
    "renater-test-and-edugain")
	url="https://metadata.federation.renater.fr/test/preview/preview-all-renater-test-metadata.xml https://metadata.federation.renater.fr/edugain/main/main-all-edugain-metadata.xml";;
    *)
	echo "Error"
        echo "Unknown federation, please update this script"
        exit 1;;
esac

if [[ $TMPDIR =~ ^// ]] || [ "x"$TMPDIR == 'x' ]
    then
    TMPDIR=$(readlink -f $PATHtoWAYF/tmp)
fi

# Check if a temp directory exists
if [ ! -d $TMPDIR ]
	then
	mkdir -p $TMPDIR
fi


# Download metadata
echo "Downloading metadata $1..."
count=0
for link in $url; do
    wget --quiet --no-check-certificate $link -O $TMPDIR/$count.xml
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
    python $GEOWAYFDIR/xmlcombine.py $args > $TMPDIR/metadata.xml
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

# Update geolocation hints
echo "Updating discojuice geolocation hints..."
$GEOWAYFDIR/discojuice/update-discojuice.sh

# Refresh WAYF's discofeed
echo "Downloading discofeeds..."
php $GEOWAYFDIR/discofeed/get-discofeed-from-array.php $GEOWAYFDIR/discofeed/known-sp.php

# Update wayf's metadata
echo "Updating WAYF's metadata..."
php $PATHtoWAYF/readMetadata.php

# Update icones and sprite sheet
echo "Updating icones and sprite sheet..."
$GEOWAYFDIR/favicon-fetcher/update-sprite-sheet.sh $TMPDIR/metadata.xml

exit 0
