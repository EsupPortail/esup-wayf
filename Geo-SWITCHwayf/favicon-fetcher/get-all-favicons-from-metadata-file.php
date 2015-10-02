<?php
//Copyright (c) 2014, SWITCH
$info = <<<SYNOPSIS
Synopsis
Extracts all Identity Provider URL (entityID or SAML2 browser-post assertion 
consumer ULR) from a SAML2 metadata file and then calls the 
get-favicon-for-URL.php script with the URLs. 

Output:
The favicons are downloaded into a directory named after the SAML2 
metadata file without the .xml extension. The files are named using the domain
name they were downloaded from. E.g. univ-rennes1.fr, ethz.ch.
The script also creates a mapping file (entityID, favicon file) in the newly 
created directory.


Usage: php get-all-favicons-from-metadata-file.php <file>
* <file> Path to a SAML2 XML metadata file

Examples:
* php get-all-favicons-from-metadata-file.php renater-metadata.xml
* php get-all-favicons-from-metadata-file.php metadata.switchaai.xml

SYNOPSIS;


if (!isset($argv[1])){
	die("Error: Invalid number of input arguments!\n\n".$info);
}

$metadataFile = $argv[1];

if (!preg_match('/(.+)\.xml$/', $metadataFile, $matches)){
	$error = 'The file '.$metadataFile.' must be a SAML2 metadata file file with the suffix .xml.';
	die($error);
}
$directory = $matches[1].'-logos';

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

// Get entityID and SSO URLs for each IdP
$metadataIDProviders = Array();
foreach( $EntityDescriptors as $EntityDescriptor ){
	$entityID = $EntityDescriptor->getAttribute('entityID');
	foreach($EntityDescriptor->childNodes as $RoleDescriptor) {
		$nodeName = $RoleDescriptor->localName;
		if($nodeName == 'IDPSSODescriptor'){
			$SSOServices = $RoleDescriptor->getElementsByTagNameNS( 'urn:oasis:names:tc:SAML:2.0:metadata', 'SingleSignOnService' );
			foreach( $SSOServices as $SSOService ){
				if ($SSOService->getAttribute('Binding') == 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'){
					$metadataIDProviders[$entityID] =  $SSOService->getAttribute('Location');
					break;
				} else if ($SSOService->getAttribute('Binding') == 'urn:mace:shibboleth:1.0:profiles:AuthnRequest'){
					$metadataIDProviders[$entityID] =  $SSOService->getAttribute('Location');
					break;
				}
			}
		}
	}
}

if (!is_dir($directory) && !mkdir($directory)) {
	die('Failed to create directory '.$directory );
}


// Include function getFavicon($url, $directory) to get favicon
require_once('get-favicon-for-URL.php');

// Call favicon download script
$counter = 0;
foreach ($metadataIDProviders as $entityID => $SSOURL){
	
	//if ($counter > 5) break;
	
	$result = '-';
	foreach (array($SSOURL) as $url){
		echo "Trying to get favicon for URL ".$url." ... ";
		
		$output = getFavicon($url, $directory);
		if ($output[0] == 0){
			echo "Done";
			$result = $output[1];

		} else {
			echo "Failed";
		}
		echo " (".$output[1].")\n";
	}
	
	
	$counter++;
}


?>
