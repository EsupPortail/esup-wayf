<?php
/******************************************************************************/
// Returns all the possible url for discofeed
function discoFeedByID($entityID){

	$shib = "Shibboleth.sso";
	$disco = "/DiscoFeed";
		
	if (!isset($entityID['ACURL'])){
		return array(1, 'No adresse to fetch discofeed.');;
	}
		
	foreach($entityID as $ACURL){
		if (strrpos($ACURL, $shib)){
			$ACURL = substr($ACURL, 0, strrpos($ACURL, $shib) + strlen($shib));
			$ACURL = $ACURL . $disco;
			$feed = fetchDiscoFeed($ACURL);
			if (!empty($feed) && isJson($feed)){
				return array(0, $feed);
			}
		}
		return array(1, 'No discofeed found.');
	}
}

/******************************************************************************/
// Returns all the possible url for discofeed
function discoFeedByACURL($ACURL){

	$shib = "Shibboleth.sso";
	$disco = "/DiscoFeed";
		
	if (strrpos($ACURL, $shib)){
		$ACURL = substr($ACURL, 0, strrpos($ACURL, $shib) + strlen($shib));
		$ACURL = $ACURL . $disco;
		$feed = fetchDiscoFeed($ACURL);
		if (!empty($feed) && isJson($feed)){
			return array(0, $feed);
		}
	}
	return array(1, 'No discofeed found.');
}


/******************************************************************************/
// Ask for JSON discofeed to the SP
function fetchDiscoFeed($url) {
	
	$ch = curl_init($url);

	curl_setopt($ch,CURLOPT_HEADER,true);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,2); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	$output = curl_exec($ch);
	$info = curl_getinfo( $ch );
	curl_close($ch);
	$json = substr($output,$info['header_size']);
	$content = json_decode($json, true);

	return $content;
}

/******************************************************************************/
// Check if the string is JSON
function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

/******************************************************************************/
// Dump variable to a file 
function dumpFile($dumpFile, $providers, $variableName){

	if(($fp = fopen($dumpFile, 'w')) !== false){
		fwrite($fp, "<?php\n\n");
		fwrite($fp, "// This file was automatically generated by discofeed-fetcher.php\n");
		fwrite($fp, "// Don't edit!\n\n");
		
		fwrite($fp, '$'.$variableName.' = ');
		fwrite($fp, var_export($providers,true));
		
		fwrite($fp, "\n?>");
			
		fclose($fp);
	} else {
		logInfo('Could not open file '.$dumpFile.' for writting');
	}
}
?>