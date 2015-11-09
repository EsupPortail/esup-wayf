<?php

	require_once('discofeed-functions.php');

	if (!isset($argv[1])){
		die ("Error: Invalid number of input arguments!\n\n".$info);
	}

	$JSONdir = dirname(__FILE__).'/feeds/';

	if (!file_exists($JSONdir)) {
		mkdir($JSONdir);
	}

	$SPURL = $argv[1];
	$disco = "/DiscoFeed";
	$curl_address = $SPURL.$disco;
	$getFeed = fetchDiscoFeed($curl_address);
	if (!empty($getFeed) && isJson($getFeed)){
		$splitedUrl = preg_split('/\//', $curl_address);
		$file = $JSONdir.$splitedUrl[2].'.json';
		echo "Fetching discofeed from ".$curl_address."\nSaving discofeed to : ".$file."\n";
		saveJSONfile($getFeed, $file);
		chmod($file, 0664);
		chgrp($file, "www-data");
	}
	else {
		echo "Could not fetch discofeed for URL : ".$curl_address . "\n";
	}

?>
