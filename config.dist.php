<?php // Copyright (c) 2014, SWITCH

//******************************************************************************
// This file contains the configuration of SWITCHwayf, a light-weight 
// implementation of a SAML Discovery Service. Adapt the settings to reflect
// your environment and then do some testing before going into production.
// Unless specifically set, default values will be used for all options.
//******************************************************************************


// 1. Language Settings
//*********************
// Language that is used by default if the language of the user's web browser
// is not available in languages.php or custom-languages.php.
// If string in local language is not available, english ('en') will be used
// as last resort.
//$defaultLanguage = 'en'; 



// 2. Cookie Settings
//*******************

// Domain within the WAYF cookie should be readable. Must start with a .
// $commonDomain = '.example.org';

// Optionnal cookie name prefix in case you run several 
// instances of the WAYF in the same domain. 
// Example: $cookieNamePrefix = '_mywayf';
//$cookieNamePrefix = '';

// Names of the cookies where to store the settings to temporarily
// redirect users transparently to their last selected IdP
//$redirectCookieName = $cookieNamePrefix.'_redirect_user_idp';

// Stores last selected IdPs 
// This value shouldn't be changed because _saml_idp is the officilly
// defined name in the SAML specification
//$SAMLDomainCookieName = $cookieNamePrefix.'_saml_idp';

// Stores last selected SP
// This value can be choosen as you like because it is something specific
// to this WAYF implementation. It can be used to display help/contact 
// information on a page in the same domain as $commonDomain by accessing
// the federation metadata and parsing out the contact information of the 
// selected IdP and SP using $SAMLDomainCookieName and $SPCookieName
//$SPCookieName = $cookieNamePrefix.'_saml_sp';

// If enabled cookies are set/transmitted only via https connections
// and the http only option is set to prevent javascripts from reading the
// cookies
//$cookieSecurity = false;

// Number of days longterm cookies should be valid
//$cookieValidity = 100;



// 3. Features and Extensions
//***************************

// Whether to show the checkbox to permanently remember a setting
//$showPermanentSetting = false;

// Whether or not to use the search-as-you-type feature of the drop down list
//$useImprovedDropDownList = true;

// Number of previously used Identity Providers to show at top of drop-down list
// Default is 3, set to 0 to disable
//$showNumOfPreviouslyUsedIdPs = 3;

// Set to true in order to enable reading the Identity Providers and Service 
// Providers from a SAML2 metadata file defined below in $metadataFile
// The parsed data will be available in $metadataIDPFile and $metadataSPFile
//$useSAML2Metadata = false; 

  // If true parsed metadata should have precedence if there are entries defined 
  // in metadata as well as the local IDProviders configuration file.
  // Requires $useSAML2Metadata to be true
  //$SAML2MetaOverLocalConf = false;

  // If includeLocalConfEntries parameter is set to true, Identity Providers
  // not listed in metadata but defined in the local IDProviders file will also
  // be displayed in the drop down list. This is required if you need to add 
  // local exceptions over the federation metadata
  // Requires $useSAML2Metadata to be true
  //$includeLocalConfEntries = true;

  // Whether the return parameter is checked against SAML2 metadata or not
  // The Discovery Service specification says the DS SHOULD check this in order
  // to mitigate phising problems.
  // The return parameter will only be checked if the Service Provider's metadata 
  // contains an <idpdisc:DiscoveryResponse> or if the assertion consumer url 
  // check below is enabled
  // Requires $useSAML2Metadata to be true
  //$enableDSReturnParamCheck = true;

    // If true, the return parameter is checked for Service Providers that
    // don't have and <idpdisc:DiscoveryResponse> extension set. Instead of this
    // extension, the hostnames of the assertion consumer URLs are used to check 
    // the return parameter against. 
    // This feature is useful in case the Service Provider's metadata doesn't contain 
    // a <idpdisc:DiscoveryResponse> extension. It increases security for Service 
    // Provider's that don't have an <idpdisc:DiscoveryResponse> extensions.
    // Requires $useSAML2Metadata and $enableDSReturnParamCheck to be true
    //$useACURLsForReturnParamCheck = false;

// Whether to turn on Kerberos support for Identity Provider preselection
//$useKerberos = false;

  // A Kerboros-protected page that redirects back to the WAYF script
  //$kerberosRedirectURL = '/myFederation/kerberosRedirect.php';

// If enabled, the user's IP is used for a reverse DNS lookup whose resulting 
// domain name then is matched with the URN values of the Identity Providers
//$useReverseDNSLookup = false;

// Whether the JavaScript required for embedding the WAYF
// on a remote site should be generated or not
// Lowers security against phising!
// If this value is set to true, any web page in the world can 
// (with some efforts) find out with a high probability from which 
// organization a user is from. This could be misused for phishing attacks. 
// Therefore, only enable this feature if you know what you are doing!
//$useEmbeddedWAYF = false;

  // If enabled the Embedded WAYF will prevent releasing information
  // about the user's preselected Identity Provider 
  // While this is benefical to the data protection of the user, it will also
  // prevent preselecting the user's Identity Provider. Thus, users will have
  // to preselect their IdP each and every time
  // Requires $useEmbeddedWAYF to be true
  //$useEmbeddedWAYFPrivacyProtection = false;

  // If enabled, the referer hostname of the request must match tan assertion 
  // consumer URL or a discovery URL of a Service Provider in $metadataSPFile
  // in order to let the Embedded WAYF preselect an Identity Provider.
  // Therefore, this option is a good compromise between data protection and
  // userfriendlyness.
  // Requires $useSAML2Metadata to be true and $useEmbeddedWAYFPrivacyProtection
  // to be false
  //$useEmbeddedWAYFRefererForPrivacyProtection = false;

// Whether or not to add the entityID of the preselected IdP to the
// exported JSON/Text/PHP Code
// Lowers security against phising!
// If this value is set to true, any web page
// in the world can easily find out with a high probability from which 
// organization a user is from. This could be misused for phishing attacks. 
// Therefore, only enable this feature if you know what you are doing!
//$exportPreselectedIdP = false;

// Whether to enable logging of WAYF/DS requests
// If turned on make sure to also configure $WAYFLogFile
//$useLogging = true; 

  // Where to log the access
  // Make sure the web server user has write access to this file!
  //$WAYFLogFile = '/var/log/apache2/wayf.log'; 


// Geo-SWITCHwayf configuration variables
// *************************************

// Temporary directory for downloads and file manipulations
// This must be an absolute path if modified
// If untouched, the path will be pathToWayf/tmp
//$tmpDir = "";

// Set to true to use discoFeed to filter IDP
//$useDiscoFeed = true;

// Set to true to retrieve geolocation data for the IDP
//$UseDiscojuiceGeolocation = true;

// Set to true if you want the block my federation to appear
//$showLocalIDPDiv = true;
  // Shibboleth's ID for local IDP
  // $LocalIDPID = "https://idp-test.univ-paris1.fr";

// Set to true if you want the block CRU Account to appear
// $showCRUAccountDiv = true;
  // Shibboleth's ID for CRU accounts
  // $CRUID = "urn:mace:cru.fr:federation:sac";

// Set to true if you want the idp search panel to be folded
//$isPanelFolded = true;


// 4. Files and path Settings
//***************************

// Set both config files to the same value if you don't want to use the 
// the WAYF to read a (potential) automatically generated file that undergoes
// some plausability checks before being used
//$IDPConfigFile = 'IDProvider.conf.php';
//$backupIDPConfigFile = 'IDProvider.conf.php';

// Use $metadataFile as source federation's metadata.
//$metadataFile = '/etc/shibboleth/metadata.myFederation.xml';

// File to store the parsed IdP list
// Will be updated automatically if the metadataFile modification time
// is more recent than this file's
// The user running the script must have permission to create $metadataIdpFile
//$metadataIDPFile = 'IDProvider.metadata.php';

// File to store the parsed SP list.
// Will be updated automatically if the metadataFile modification time
// is more recent than this file's
// The user running the script must have permission to create $metadataIdpFile
//$metadataSPFile = 'SProvider.metadata.php';

// File to use as the lock file for writing the parsed IdP and SP lists.
// The user running the script must have permission to write $metadataLockFile
//$metadataLockFile = '/tmp/wayf_metadata.lock';

// Use an absolute URL in case you want to use the embedded WAYF
//$imageURL = 'https://ds.example.org/SWITCHwayf/images';

// Absolute URL to point to css directory
//$cssURL = 'https://ds.example.org/SWITCHwayf/css';

// Absolute URL to point to javascript directory
//$javascriptURL = 'https://ds.example.org/SWITCHwayf/js';



// 5. Appearance Settings
//**************************

// Name of the federation
//$federationName = 'myFederation';

// URL to send user to when clicking on federation logo
// Insert %s as macro to be substituted by the language (e.g. 'en', 'de', 'fr', ...) the WAYF uses
// Set to an empty string to hide the logo
//$federationURL = 'http://www.example.org/myFed/';

// Absolute URL to the federation logo that should be displayed in the Embedded WAYF
// Set to an empty string to hide the logo
//$logoURL = 'http://ds.example.org/SWITCHwayf/images/federation-logo.png';

// Absolute URL to the small federation logo that should be displayed in the 
// embedded WAYF. Make sure the dimensions (in particular the height of the logo)
// is small, ideally not larger than 120x30 pixel
//$smallLogoURL = 'http://ds.example.org/SWITCHwayf/images/small-federation-logo.png';

// Support contact email address
//$supportContactEmail = 'helpdesk@example.org';

// Absolute URL to the logo of the organization operating this Discovery Service
// Set to an empty string to hide the logo
//$organizationLogoURL = 'https://ds.example.org/SWITCHwayf/images/organization-logo.png'; 

// Absolute URL to the organization's web page
// Insert %s as macro to be substituted by the language (e.g. 'en', 'de', 'fr', ...) the WAYF uses
//$organizationURL = 'http://www.example.org/'; 

// Absolute URL to an FAQ page
// This entries local string is 'faq' in languages.php
// Insert %s as macro to be substituted by the language (e.g. 'en', 'de', 'fr', ...) the WAYF uses
// Set to an empty string to hide the logo
//$faqURL = 'http://www.example.org/%s/myFed/faq/';

// Absolute URL to a help/support page
// Insert %s as macro to be substituted by the language (e.g. 'en', 'de', 'fr', ...) the WAYF uses
// Set to an empty string to hide the logo
//$helpURL = 'http://www.example.org/%s/myFed/help/';

// Absolute URL to a privacy policy page
// Insert %s as macro to be substituted by the language (e.g. 'en', 'de', 'fr', ...) the WAYF uses
// Set to an empty string to hide the logo
//$privacyURL = 'http://www.example.org/%s/myFed/privacy/';



// Development mode settings
//**************************
// If the development mode is activated, PHP errors and warnings will be displayed
// on pages the SWITCHwayf generates
//$developmentMode = false;

?>
