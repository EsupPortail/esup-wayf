<?php

require_once('discofeed-functions.php');

$info = <<<SYNOPSIS
Synopsis
Fetch all the discofeeds from the different SP in the XML file passed in parameter, 
and parse them in an array.

Output:
The output is a file "discofeed.metadata.php". It contains an array with the entity IDs as keys and the urls 
and feeds as values.

Usage: php discofeed-fetcher.php discofeed.metadata.php


SYNOPSIS;

if (!isset($argv[1])){
	die("Error: Invalid number of input arguments!\n\n".$info);
}

$discofeedFile = $argv[1];

$newDiscofeedArray = Array();

if (!file_exists($discofeedFile)){
	echo "Creating a new file ".$discofeedFile." to store discofeed data.\n\n";
	dumpFile($discofeedFile, $newDiscofeedArray, 'discofeedArray');
}
else {

	echo "Updating discofeed from array...\n";

	if (!preg_match('/(.+)\.php$/', $discofeedFile)){
		die("Must be a path to the php file containning the discofeed array.\n");
	}

	require_once($discofeedFile);

	foreach ($discofeedArray as $key => $val){
		echo $val['ACURL'];
		$newDiscofeedArray[$key]['ACURL'] = $val['ACURL'];
		$output = discoFeedByACURL($val['ACURL']);
		if ($output[0] == 0){
			echo "Discofeed ".$key." saved\n";
			$newDiscofeedArray[$key]['FEED'] = $output[1];

		} else {
			echo "Discofeed ".$key." failed (".$output[1].")\n";
		}
	}

	if(is_array($newDiscofeedArray) && !empty($newDiscofeedArray)){ 
		echo 'Dumping parsed array to file '.$discofeedFile."\n";
		dumpFile($discofeedFile, $newDiscofeedArray, 'discofeedArray');
	}

}


?>