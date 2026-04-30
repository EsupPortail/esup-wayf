<?php // Copyright (c) 2014, SWITCH

// This file is used to dynamically create the list of IdPs and SP to be 
// displayed for the WAYF/DS service based on the federation metadata.
// Configuration parameters are specified in config.php.
//
// The list of Identity Providers can also be updated by running the script
// readMetadata.php periodically as web server user, e.g. with a cron entry like:
// 5 * * * * /usr/bin/php readMetadata.php > /dev/null

require_once('functions.php');
require_once('config.php');

// Init log file
openlog("SWITCHwayf.readMetadata.php", LOG_PID | LOG_PERROR, LOG_LOCAL0);

// Make sure this script is not accessed directly
if(isRunViaCLI()){
	// Run in cli mode.
	// Could be used for testing purposes or to facilitate startup confiduration.
	// Results are dumped in $metadataIDPFile (see config.php)
	
	// Set dummy server name
	$_SERVER['SERVER_NAME'] = 'localhost';
	
	// Set default config options
	initConfigOptions();
	
	// Load Identity Providers
	require($IDPConfigFile);
	
	// Check that $IDProviders exists
	if (!isset($IDProviders) or !is_array($IDProviders)){
		$IDProviders = array();
	}

	$metadataFile = $argv[1];
	$discoJuiceDir = $argv[2];

	if (
		   !file_exists($metadataFile) 
		|| trim(@file_get_contents($metadataFile)) == '') {
	  exit ("Exiting: File ".$metadataFile." is empty or does not exist\n");
	}
	
	echo 'Parsing metadata file '.$metadataFile."\n";
	list($metadataIDProviders, $metadataSProviders) = parseMetadata($metadataFile, $defaultLanguage);

	// Enrich with CAT eduroam geolocation first (highest priority, requires network access)
	if (!empty($UseCatEduroamGeolocation)) addCatEduroamGeolocation($metadataIDProviders);

	// Fall back to discojuice for any remaining IdPs without geolocation
	if ($UseDiscojuiceGeolocation) addDiscojuiceGeolocation($metadataIDProviders);
	
	// If $metadataIDProviders is not FALSE, dump results in $metadataIDPFile.
	if(is_array($metadataIDProviders)){ 
		
		echo 'Dumping parsed Identity Providers to file '.$metadataIDPFile."\n";
		dumpFile($metadataIDPFile, $metadataIDProviders, 'metadataIDProviders');
	}
	// If $metadataSProviders is not FALSE, dump results in $metadataSPFile.
	if(is_array($metadataSProviders)){ 
		
		echo 'Dumping parsed Service Providers to file '.$metadataSPFile."\n";
		dumpFile($metadataSPFile, $metadataSProviders, 'metadataSProviders');
	}

	// If $metadataIDProviders is not FALSE, update $IDProviders and print the Identity Providers lists.
	if(is_array($metadataIDProviders)){ 

		echo 'Merging parsed Identity Providers with data from file '.$metadataFile."\n";
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);

		/*echo "Printing parsed Identity Providers:\n";
		print_r($metadataIDProviders);
		
		echo "Printing effective Identity Providers:\n";
		print_r($IDProviders);*/
	}
	
	// If $metadataSProviders is not FALSE, update $SProviders and print the list.
	if(is_array($metadataSProviders)){ 
		
		// Fow now copy the array by reference
		$SProviders = &$metadataSProviders;

		/*echo "Printing parsed Service Providers:\n";
		print_r($metadataSProviders);*/
	}
	
	
} elseif (isRunViaInclude()) {
	
	// Check that $IDProviders exists
	if (!isset($IDProviders) or !is_array($IDProviders)){
		$IDProviders = array();
	}
	
	// Run as included file
	if(!file_exists($metadataIDPFile)){

		die("you must run Geo-SWITCHwayf/update.sh in a cron");
		
	} elseif (file_exists($metadataIDPFile)){
		
		// Read SP and IDP files generated with metadata
		require($metadataIDPFile);
		if (file_exists($metadataSPFile)){
			require($metadataSPFile);
		}

		// Now merge IDPs from metadata and static file
		$IDProviders = mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries);
		
		// Fow now copy the array by reference
		$SProviders = &$metadataSProviders;
	}
	
} else {
	exit('No direct script access allowed');
}

closelog();

/*****************************************************************************/
// Function parseMetadata, parses metadata file and returns Array($IdPs, SPs)  or
// Array(false, false) if error occurs while parsing metadata file
function parseMetadata($metadataFile, $defaultLanguage){
	$metadataSProviders = array();
	if(!file_exists($metadataFile)){
		$errorMsg = 'File '.$metadataFile." does not exist"; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}

	if(!is_readable($metadataFile)){
		$errorMsg = 'File '.$metadataFile." cannot be read due to insufficient permissions"; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}
	
	$CurrentXMLReaderNode = new XMLReader();
	if(!$CurrentXMLReaderNode->open($metadataFile, null, LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING | 1)){
		$errorMsg = 'Could not parse metadata file '.$metadataFile; 
		if (isRunViaCLI()){
			echo $errorMsg."\n";
		} else {
			logError($errorMsg);
		}
		return Array(false, false);
	}
	
	// Process individual EntityDescriptors
	while( $CurrentXMLReaderNode->read() ) {
		if($CurrentXMLReaderNode->nodeType == XMLReader::ELEMENT && $CurrentXMLReaderNode->localName  === 'EntityDescriptor') {
			$entityID = $CurrentXMLReaderNode->getAttribute('entityID');
			$EntityDescriptorXML = $CurrentXMLReaderNode->readOuterXML();
			$EntityDescriptorDOM = new DOMDocument();
			$EntityDescriptorDOM->loadXML($EntityDescriptorXML);
			
			// Check role descriptors
			foreach($EntityDescriptorDOM->documentElement->childNodes as $RoleDescriptor) {
				$nodeName = $RoleDescriptor->localName;
				switch($nodeName){
					case 'IDPSSODescriptor':
						$IDP = processIDPRoleDescriptor($RoleDescriptor);
						if ($IDP){
							$metadataIDProviders[$entityID] = $IDP;
						}
						break;
					case 'SPSSODescriptor':
						$SP = processSPRoleDescriptor($RoleDescriptor);
						if ($SP){
							$metadataSProviders[$entityID] = $SP;
						} else {
							$errorMsg = "Failed to load SP with entityID $entityID from metadata file $metadataFile";
							if (isRunViaCLI()){
								echo $errorMsg."\n";
							} else {
								logWarning($errorMsg);
							}
						}
						break;
					default:
				}
			}
		}
	}
	
	// Output result
	$infoMsg = "Successfully parsed metadata file ".$metadataFile. ". Found ".count($metadataIDProviders)." IdPs and ".count($metadataSProviders)." SPs";
	if (isRunViaCLI()){
		echo $infoMsg."\n";
	} else {
		logInfo($infoMsg);
	}
	
	
	return Array($metadataIDProviders, $metadataSProviders);
}

/******************************************************************************/
// Is this script run in CLI mode
function isRunViaCLI(){
	return !isset($_SERVER['REMOTE_ADDR']);
}

/******************************************************************************/
// Is this script run in CLI mode
function isRunViaInclude(){
	return basename($_SERVER['SCRIPT_NAME']) != 'readMetadata.php';
}

/******************************************************************************/
// Processes an IDPRoleDescriptor XML node and returns an IDP entry or false if 
// something went wrong
function processIDPRoleDescriptor($IDPRoleDescriptorNode){
	global $defaultLanguage;
	
	$IDP = Array();
	$Profiles = Array();

	// Get SSO URL
	$SSOServices = $IDPRoleDescriptorNode->getElementsByTagNameNS( 'urn:oasis:names:tc:SAML:2.0:metadata', 'SingleSignOnService' );
	foreach( $SSOServices as $SSOService ){
	  $Profiles[$SSOService->getAttribute('Binding')] = $SSOService->getAttribute('Location');
	}
	
	// Set SAML1 SSO URL
	if (isset($Profiles['urn:mace:shibboleth:1.0:profiles:AuthnRequest'])) {
		$IDP['SSO'] = $Profiles['urn:mace:shibboleth:1.0:profiles:AuthnRequest'];
	} else if ($Profiles['urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect']) {
		$IDP['SSO'] = $Profiles['urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'];
	} else {
		$IDP['SSO'] = 'https://no.saml1.or.saml2.sso.url.defined.com/error';
	}
	
	// First get MDUI name
	$MDUIDisplayNames = getMDUIDisplayNames($IDPRoleDescriptorNode);
	if (count($MDUIDisplayNames)){
		$IDP['Name'] = current($MDUIDisplayNames);
	}
	foreach ($MDUIDisplayNames as $lang => $value){
		$IDP[$lang]['Name'] = $value;
	}
	
	// Then try organization names 
	if (empty($IDP['Name'])){
		$OrgnizationNames = getOrganizationNames($IDPRoleDescriptorNode);
		$IDP['Name'] = current($OrgnizationNames);
		
		foreach ($OrgnizationNames as $lang => $value){
			$IDP[$lang]['Name'] = $value;
		}
	} 
	
	// As last resort, use entityID
	if (empty($IDP['Name'])){
		$IDP['Name'] = $IDPRoleDescriptorNode->parentNode->getAttribute('entityID');
	}
	
	// Set default name
	if (isset($IDP[$defaultLanguage])){
		$IDP['Name'] = $IDP[$defaultLanguage]['Name'];
	} elseif (isset($IDP['en'])){
		$IDP['Name'] = $IDP['en']['Name'];
	}
	
	// Get supported protocols
	$protocols = $IDPRoleDescriptorNode->getAttribute('protocolSupportEnumeration');
	$IDP['Protocols'] = $protocols;
	
	// Get keywords
	$MDUIKeywords = getMDUIKeywords($IDPRoleDescriptorNode);
	foreach ($MDUIKeywords as $lang => $keywords){
		$IDP[$lang]['Keywords'] = $keywords;
	}
	
	// Get Logos
	$MDUILogos = getMDUILogos($IDPRoleDescriptorNode);
	foreach ($MDUILogos as $Logo){
		// Skip non-favicon logos
		if ($Logo['Height'] != 16 || $Logo['Width'] != 16 ){
			continue;
		}
		
		// Strip height and width
		unset($Logo['Height']);
		unset($Logo['Width']);
		
		if ($Logo['Lang'] == ''){
			unset($Logo['Lang']);
			$IDP['Logo'] = $Logo;
		} else {
			$lang = $Logo['Lang'];
			unset($Logo['Lang']);
			$IDP[$lang]['Logo'] = $Logo;
		}
	}
	
	// Get AttributeValue 
	$SAMLAttributeValues = getSAMLAttributeValues($IDPRoleDescriptorNode);
	if ($SAMLAttributeValues){
		$IDP['AttributeValue'] = $SAMLAttributeValues;
	}
	
	// Get IPHints 
	$MDUIIPHints = getMDUIIPHints($IDPRoleDescriptorNode);
	if ($MDUIIPHints){
		$IDP['IPHint'] = $MDUIIPHints;
	}
	
	// Get DomainHints 
	$MDUIDomainHints = getMDUIDomainHints($IDPRoleDescriptorNode);
	if ($MDUIDomainHints){
		$IDP['DomainHint'] = $MDUIDomainHints;
	}
	
	// Get GeolocationHints 
	$MDUIGeolocationHints = getMDUIGeolocationHints($IDPRoleDescriptorNode);
	if ($MDUIGeolocationHints){
		$IDP['GeolocationHint'] = $MDUIGeolocationHints;
	}

	// Get Shibboleth scopes (used for domain-based matching against CAT eduroam)
	$shibmdScopes = getShibmdScopes($IDPRoleDescriptorNode);
	if ($shibmdScopes) {
		$IDP['Scope'] = $shibmdScopes;
	}

	return $IDP;
}

/******************************************************************************/
// Processes an SPRoleDescriptor XML node and returns an SP entry or false if 
// something went wrong
function processSPRoleDescriptor($SPRoleDescriptorNode){
	global $defaultLanguage;

	$SP = Array();
	
	// Get <idpdisc:DiscoveryResponse> extensions
	$DResponses = $SPRoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol', 'DiscoveryResponse');
	foreach( $DResponses as $DResponse ){
		if ($DResponse->getAttribute('Binding') == 'urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol'){
			$SP['DSURL'][] =  $DResponse->getAttribute('Location');
		}
	}
	
	// First get MDUI name
	$MDUIDisplayNames = getMDUIDisplayNames($SPRoleDescriptorNode);
	if (count($MDUIDisplayNames)){
		$SP['Name'] = current($MDUIDisplayNames);
	}
	foreach ($MDUIDisplayNames as $lang => $value){
		$SP[$lang]['Name'] = $value;
	}
	
	// Then try attribute consuming service
	if (empty($SP['Name'])){
		$ConsumingServiceNames = getAttributeConsumingServiceNames($SPRoleDescriptorNode);
		$SP['Name'] = current($ConsumingServiceNames);
		
		foreach ($ConsumingServiceNames as $lang => $value){
			$SP[$lang]['Name'] = $value;
		}
	} 
	
	// As last resort, use entityID
	if (empty($SP['Name'])){
		$SP['Name'] = $SPRoleDescriptorNode->parentNode->getAttribute('entityID');
	}
	
	// Set default name
	if (isset($SP[$defaultLanguage])){
		$SP['Name'] = $SP[$defaultLanguage]['Name'];
	} elseif (isset($SP['en'])){
		$SP['Name'] = $SP['en']['Name'];
	}
	
	// Get Assertion Consumer Services and store their hostnames
	$ACServices = $SPRoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'AssertionConsumerService');
	foreach( $ACServices as $ACService ){
		$SP['ACURL'][] =  $ACService->getAttribute('Location');
	}
	
	// Get supported protocols
	$protocols = $SPRoleDescriptorNode->getAttribute('protocolSupportEnumeration');
	$SP['Protocols'] = $protocols;
	
	// Get keywords
	$MDUIKeywords = getMDUIKeywords($SPRoleDescriptorNode);
	foreach ($MDUIKeywords as $lang => $keywords){
		$SP[$lang]['Keywords'] = $keywords;
	}
	
	return $SP;
}


/******************************************************************************/
// Function mergeInfo is used to create the effective $IDProviders array.
// For each IDP found in the metadata, merge the values from IDProvider.conf.php.
// If an IDP is found in IDProvider.conf as well as in metadata, use metadata  
// information if $SAML2MetaOverLocalConf is true or else use IDProvider.conf data
function mergeInfo($IDProviders, $metadataIDProviders, $SAML2MetaOverLocalConf, $includeLocalConfEntries){

	// If $includeLocalConfEntries parameter is set to true, mergeInfo() will also consider IDPs
	// not listed in metadataIDProviders but defined in IDProviders file
	// This is required if you need to add local exceptions over the federation metadata
	$allIDPS = $metadataIDProviders;
	$mergedArray = Array();
	if ($includeLocalConfEntries) {
		$allIDPS = array_merge($metadataIDProviders, $IDProviders);
	}
	
	foreach ($allIDPS as $allIDPsKey => $allIDPsEntry){
		if(isset($IDProviders[$allIDPsKey])){
			// Entry exists also in local IDProviders.conf.php
			if (isset($metadataIDProviders[$allIDPsKey]) && is_array($metadataIDProviders[$allIDPsKey])) {
				
				// Remove IdP if there is a removal rule in local IDProviders.conf.php 
				if (!is_array($IDProviders[$allIDPsKey])){
					unset($metadataIDProviders[$allIDPsKey]);
					continue;
				}
				
				// Entry exists in both IDProviders sources and is an array
				if($SAML2MetaOverLocalConf){
					// Metadata entry overwrite local conf
					$mergedArray[$allIDPsKey] = array_merge($IDProviders[$allIDPsKey], $metadataIDProviders[$allIDPsKey]);
				} else {
					// Local conf overwrites metada entry
					$mergedArray[$allIDPsKey] = array_merge($metadataIDProviders[$allIDPsKey], $IDProviders[$allIDPsKey]);
				}
			} else {
					// Entry only exists in local IDProviders file
					$mergedArray[$allIDPsKey] = $IDProviders[$allIDPsKey];
					$mergedArray[$allIDPsKey]['local'] = true;
			}
		} else {
			// Entry doesnt exist in in local IDProviders.conf.php
			$mergedArray[$allIDPsKey] = $metadataIDProviders[$allIDPsKey];
		}
	}
	
	return $mergedArray;
}

/******************************************************************************/
// Get MD Display Names from RoleDescriptor
function getMDUIDisplayNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIDisplayNames = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'DisplayName');
	foreach( $MDUIDisplayNames as $MDUIDisplayName ){
		$lang = $MDUIDisplayName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($MDUIDisplayName->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Keywords from RoleDescriptor
function getMDUIKeywords($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIKeywords = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'Keywords');
	foreach( $MDUIKeywords as $MDUIKeywordEntry ){
		$lang = $MDUIKeywordEntry->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($MDUIKeywordEntry->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Logos from RoleDescriptor. Prefer the favicon logos
function getMDUILogos($RoleDescriptorNode){
	
	$Logos = Array();
	$MDUILogos = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'Logo');
	foreach( $MDUILogos as $MDUILogoEntry ){
		$Logo = Array();
		$Logo['URL'] = trimToSingleLine($MDUILogoEntry->nodeValue);
		$Logo['Height'] = ($MDUILogoEntry->getAttribute('height') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('height')) : '16';
		$Logo['Width'] = ($MDUILogoEntry->getAttribute('width') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('width')) : '16';
		$Logo['Lang'] = ($MDUILogoEntry->getAttribute('lang') != '') ? trimToSingleLine($MDUILogoEntry->getAttribute('lang')) : '';
		$Logos[] = $Logo;
	}
	
	return $Logos;
}


/******************************************************************************/
// Get MD Attribute Value(kind) from RoleDescriptor
function getSAMLAttributeValues($RoleDescriptorNode){
	
	$Entity = Array();
	
	$SAMLAttributeValues = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:assertion', 'AttributeValue');
	foreach( $SAMLAttributeValues as $SAMLAttributeValuesEntry ){
		$Entity[] = trimToSingleLine($SAMLAttributeValuesEntry->nodeValue);
	}
	
	return $Entity;
}


/******************************************************************************/
// Get MD IP Address Hints from RoleDescriptor
function getMDUIIPHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIIPHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'IPHint');
	foreach( $MDUIIPHints as $MDUIIPHintEntry ){
		if (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2}$/", trimToSingleLine($MDUIIPHintEntry->nodeValue), $splitIP)){
			$Entity[] = trimToSingleLine($splitIP[0]);
		} elseif (preg_match("/^.*\:.*\/[0-9]{1,2}$/", trimToSingleLine($MDUIIPHintEntry->nodeValue), $splitIP)){ 
			$Entity[] = trimToSingleLine($splitIP[0]);
		}
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Domain Hints from RoleDescriptor
function getMDUIDomainHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIDomainHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'DomainHint');
	foreach( $MDUIDomainHints as $MDUIDomainHintEntry ){
		$Entity[] = trimToSingleLine($MDUIDomainHintEntry->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get MD Geolocation Hints from RoleDescriptor
function getMDUIGeolocationHints($RoleDescriptorNode){
	
	$Entity = Array();
	
	$MDUIGeolocationHints = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:metadata:ui', 'GeolocationHint');
	foreach( $MDUIGeolocationHints as $MDUIGeolocationHintEntry ){
		if (preg_match("/^geo:([0-9]+\.{0,1}[0-9]*,[0-9]+\.{0,1}[0-9]*)$/", trimToSingleLine($MDUIGeolocationHintEntry->nodeValue), $splitGeo)){
			$Entity[] = trimToSingleLine($splitGeo[1]);
		}
	}
	
	return $Entity;
}

/******************************************************************************/
// Get Organization Names from RoleDescriptor
function getOrganizationNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$Orgnization = $RoleDescriptorNode->parentNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'Organization' )->item(0);
	if ($Orgnization){
		$DisplayNames = $Orgnization->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'OrganizationDisplayName');
		foreach ($DisplayNames as $DisplayName){
			$lang = $DisplayName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
			$Entity[$lang] = trimToSingleLine($DisplayName->nodeValue);
		}
	}
	
	return $Entity;
}


/******************************************************************************/
// Get Attribute Consuming Service
function getAttributeConsumingServiceNames($RoleDescriptorNode){
	
	$Entity = Array();
	
	$ServiceNames = $RoleDescriptorNode->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:metadata', 'ServiceName' );
	foreach ($ServiceNames as $ServiceName){
		$lang = $ServiceName->getAttributeNodeNS('http://www.w3.org/XML/1998/namespace', 'lang')->nodeValue;
		$Entity[$lang] = trimToSingleLine($ServiceName->nodeValue);
	}
	
	return $Entity;
}

/******************************************************************************/
// Get GeolocationHint from discojuice feed (rely on discojuiceGeolocation/update.sh run in a cron)
function addDiscojuiceGeolocation(&$metadataIDProviders) {
	global $discoJuiceDir;
	foreach (glob("$discoJuiceDir/*.json") as $file) {
		foreach (json_decode(file_get_contents($file)) as $e) {
			if (!isset($metadataIDProviders[$e->entityID])) continue;
			$IDP = &$metadataIDProviders[$e->entityID];
			if (isset($IDP['GeolocationHint'])) continue;

			$IDP['GeolocationHint'] = isset($e->geo)?$e->geo->lat . "," . $e->geo->lon:null;
		}
	}
}

/******************************************************************************/
// Extract shibmd:Scope values from an IDPSSODescriptor XML node.
// Only non-regexp scopes are returned, as regexp scopes cannot be used
// for direct domain matching against external databases like CAT eduroam.
// Namespace: urn:mace:shibboleth:metadata:1.0
function getShibmdScopes($RoleDescriptorNode) {
	$scopes = array();
	$scopeNodes = $RoleDescriptorNode->getElementsByTagNameNS(
		'urn:mace:shibboleth:metadata:1.0',
		'Scope'
	);
	foreach ($scopeNodes as $scopeNode) {
		// Skip regexp scopes — they cannot be matched directly as domain names
		if (strtolower($scopeNode->getAttribute('regexp')) === 'true') continue;
		$scope = strtolower(trimToSingleLine($scopeNode->nodeValue));
		if ($scope !== '') {
			$scopes[] = $scope;
		}
	}
	return $scopes;
}

/******************************************************************************/
// Enriches IdP entries with geolocation data fetched from the CAT eduroam API.
//
// IMPORTANT: The CAT API returns a numeric internal ID in the 'entityID' field,
// NOT a SAML entityID. Matching against SAML IdPs is performed via domain names
// found in the CAT 'keywords' field (e.g. "univ-paris1.fr").
//
// Matching strategy (in decreasing order of reliability):
//   1. mdui:DomainHint values — explicitly declared by the IdP operator
//   2. shibmd:Scope values declared in the IdP metadata
//   3. Hostname extracted from entityID URL, with progressive subdomain stripping
//      (e.g. "idp.univ-paris1.fr" -> "univ-paris1.fr")
//
// Only IdPs that do not already have a GeolocationHint are processed.
// This function must be called BEFORE addDiscojuiceGeolocation() so that
// CAT data takes priority over discojuice data.
function addCatEduroamGeolocation(&$metadataIDProviders) {
	global $catEduroamApiUrl;
	$apiUrl = !empty($catEduroamApiUrl)
		? $catEduroamApiUrl
		: 'https://cat.eduroam.org/user/API.php?action=listAllIdentityProviders&api_version=2&lang=en';

	// Fetch the CAT API with reasonable timeouts
	if (!function_exists('curl_init')) {
		syslog(LOG_WARNING, 'addCatEduroamGeolocation: cURL is not available, skipping CAT geolocation');
		return;
	}
	$ch = curl_init($apiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_USERAGENT, 'esup-wayf/readMetadata');
	$output   = curl_exec($ch);
	$curlErr  = curl_error($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if (!$output || $curlErr || $httpCode !== 200) {
		syslog(LOG_WARNING, "addCatEduroamGeolocation: failed to fetch CAT API (http=$httpCode, err=$curlErr)");
		return;
	}

	$data = json_decode($output);
	if (!$data || !is_array($data)) {
		syslog(LOG_WARNING, 'addCatEduroamGeolocation: invalid JSON from CAT API');
		return;
	}

	// Build a flat lookup map: domain (lowercase) => "lat,lon"
	// CAT keywords is an array of arrays; extract domain-like strings only.
	// A domain-like string: no spaces, contains a dot, only alphanumeric/hyphen/dot chars.
	$catGeoMap = array();
	foreach ($data as $inst) {
		if (!isset($inst->geo) || !is_array($inst->geo) || count($inst->geo) === 0) continue;
		if (!isset($inst->keywords) || !is_array($inst->keywords)) continue;
		$geo = $inst->geo[0];
		if (!isset($geo->lat, $geo->lon)) continue;
		$geoStr = $geo->lat . ',' . $geo->lon;

		// The CAT API v2 keywords field may be structured in two ways:
		//   - Flat array of strings: ["domain1.fr", "domain2.fr"]
		//   - Array of arrays (one per language): [["en","dom1"],["fr","dom1"]]
		// We normalise both cases into a flat list of candidate strings.
		$flatKeywords = array();
		foreach ($inst->keywords as $keywordGroup) {
			if (is_array($keywordGroup)) {
				// Nested structure: each sub-array may start with a language code
				foreach ($keywordGroup as $keyword) {
					$flatKeywords[] = (string) $keyword;
				}
			} else {
				// Flat structure: each element is already a keyword string
				$flatKeywords[] = (string) $keywordGroup;
			}
		}

		foreach ($flatKeywords as $keyword) {
			$kw = strtolower(trim($keyword));
			// Accept only domain-like strings (no spaces, at least one dot, valid charset)
			if (strpos($kw, ' ') === false
				&& strpos($kw, '.') !== false
				&& preg_match('/^[a-z0-9._-]+$/', $kw)
			) {
				// First occurrence wins
				if (!isset($catGeoMap[$kw])) {
					$catGeoMap[$kw] = $geoStr;
				}
			}
		}
	}

	if (empty($catGeoMap)) {
		syslog(LOG_WARNING, 'addCatEduroamGeolocation: CAT domain map is empty, skipping enrichment');
		return;
	}

	$matched = 0;
	foreach ($metadataIDProviders as $entityID => &$IDP) {
		// Skip IdPs that already carry a geolocation hint
		if (!empty($IDP['GeolocationHint'])) continue;

		$candidates = array();

		// 1. mdui:DomainHint values — explicitly declared by the IdP operator, very reliable
		if (!empty($IDP['DomainHint']) && is_array($IDP['DomainHint'])) {
			foreach ($IDP['DomainHint'] as $dh) {
				$candidates[] = strtolower(trim($dh));
			}
		}

		// 2. shibmd:Scope values — declared in federation metadata, mirrors institution domain
		if (!empty($IDP['Scope']) && is_array($IDP['Scope'])) {
			foreach ($IDP['Scope'] as $scope) {
				$candidates[] = strtolower(trim($scope));
			}
		}

		// 3. Hostname derived from entityID URL, with progressive subdomain stripping
		//    e.g. "https://idp.univ-paris1.fr/idp/shibboleth" gives:
		//         "idp.univ-paris1.fr", then "univ-paris1.fr"
		$host = strtolower((string) parse_url($entityID, PHP_URL_HOST));
		if ($host !== '') {
			$candidates[] = $host;
			$parts     = explode('.', $host);
			$partCount = count($parts);
			// Strip leading subdomains while keeping at least two labels
			for ($i = 1; $i < $partCount - 1; $i++) {
				$candidates[] = implode('.', array_slice($parts, $i));
			}
		}

		// Try each candidate against the CAT domain map (first match wins)
		foreach (array_unique($candidates) as $candidate) {
			if (isset($catGeoMap[$candidate])) {
				$IDP['GeolocationHint'] = $catGeoMap[$candidate];
				$matched++;
				break;
			}
		}
	}
	unset($IDP);

	syslog(LOG_INFO, "addCatEduroamGeolocation: enriched $matched IdP(s) with CAT eduroam geolocation");
}

?>
