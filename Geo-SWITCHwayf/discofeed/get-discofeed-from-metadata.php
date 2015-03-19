<?php

$info = <<<SYNOPSIS
Synopsis
Fetch all the discofeeds from the different SP in the XML file passed in parameter, 
and parse them in an array.

Output:
The output is a file "discofeed.metadata.xml". It contains an array with the entity IDs as keys and the urls 
and feeds as values.


Usage: php discofeed-fetcher.php metadata.xml


SYNOPSIS;

if (!isset($argv[1])){
	die("Error: Invalid number of input arguments!\n\n".$info);
}

$discofeedFile = dirname(__FILE__).'/discofeed.metadata.php';

echo "Fetching discofeed from metadata..."
$metadataFile = $argv[1];
if (!preg_match('/(.+)\.xml$/', $metadataFile, $matches)){
	$error = 'The file '.$metadataFile.' must be a SAML2 metadata file file with the suffix .xml.';
	die($error);
}

if (!file_exists($metadataFile)){
	$error = 'The file '.$metadataFile.' does not exist';
	die($error);
}

if (!is_readable($metadataFile)){
	$error = 'The file '.$metadataFile.' cannot be read.';
	die($error);
}

$doc = new DOMDocument();
if(!$doc->load( $metadataFile )){
	$error = 'Could not load file '.$metadataFile.' as XML file.';
	die($error);
}

$EntityDescriptors = $doc->getElementsByTagNameNS( 'urn:oasis:names:tc:SAML:2.0:metadata', 'EntityDescriptor' );

$metadataSProviders = Array();
foreach( $EntityDescriptors as $EntityDescriptor ){
	$entityID = $EntityDescriptor->getAttribute('entityID');
	foreach($EntityDescriptor->childNodes as $RoleDescriptor) {
		$nodeName = $RoleDescriptor->localName;
		if($nodeName == 'SPSSODescriptor'){
			$ACServices = $RoleDescriptor->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'AssertionConsumerService');
			foreach( $ACServices as $ACService ){
				$metadataSProviders[$entityID]['ACURL'] =  $ACService->getAttribute('Location');
			}
		}
	}
}

require_once('discofeed-functions.php');


foreach ($metadataSProviders as $key => $val){

	$output = discoFeedByID($val);
	if ($output[0] == 0){
		echo "Discofeed ".$key." saved\n";
		$metadataSProviders[$key]['FEED'] = $output[1];

	} else {
		echo "Discofeed ".$key." failed (".$output[1].")\n";
	}
}

if(is_array($metadataSProviders)){ 
	echo 'Dumping parsed array to file '.$discofeedFile."\n";
	dumpFile($discofeedFile, $metadataSProviders, 'discofeedArray');
}

?>