<?php

require_once('discofeed-functions.php');

if (!isset($argv[1])){
	die ("Error: Invalid number of input arguments!\n\n".$info);
}

$SPlist = $argv[1];

if (!file_exists($SPlist)){
	die ("Error : File ".$SPlist." doesn't exits. Exiting.");
}
else {
	
	require_once($SPlist);
	$JSONdir = dirname(__FILE__).'/feeds/';

	if (!file_exists($JSONdir)) {
		mkdir($JSONdir);
	}

	$disco = "/DiscoFeed";

	foreach ($knownSP as $key => $value) {
		$curl_address = $value.$disco;
		$getFeed = fetchDiscoFeed($curl_address);
		if (!empty($getFeed) && isJson($getFeed)){
			$splitedUrl = preg_split('/\//', $value);
			$file = $JSONdir.$splitedUrl[2].'.json';
			echo " > Fetching and saving discofeed from ".$curl_address." to ".$file."\n";
			saveJSONfile($getFeed, $file);
		}
		else {
			echo " > Could not fetch discofeed for URL : ".$curl_address . "\n";
		}
	}
}

?>