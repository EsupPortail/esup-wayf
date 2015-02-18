<?php // Copyright (c) 2014, SWITCH

// WAYF Identity Provider Configuration file

// Find below some example entries of Identity Providers, categories and 
// cascaded WAYFs
// The keys of $IDProviders must correspond to the entityId of the 
// Identity Providers or a unique value in case of a cascaded WAYF/DS or 
// a category. In the case of a category, the key must correspond to the the 
// Type value of Identity Provider entries.
// The sequence of IdPs and SPs play a role. No sorting is done.
// 
// Please read the file DOC for information on the format of the entries

// Category
$IDProviders['unknown'] = array (
		'Type' => 'category',
		'Name' => 'Others',
		'de' => array ('Name' => 'Andere'),
		'fr' => array ('Name' => 'Autres'),
		'it' => array ('Name' => 'Altri'),
);

$IDProviders['urn:mace:cru.fr:federation:sac'] = array(
    'SSO' => 'https://cru.renater.fr/idp/profile/Shibboleth/SSO',
    'Name' => 'Comptes CRU',
    'en' => 
    array (
      'Name' => 'CRU accounts',
    ),
    'fr' => 
    array (
      'Name' => 'Comptes CRU',
    ),
    'Protocols' => 'urn:oasis:names:tc:SAML:1.1:protocol urn:mace:shibboleth:1.0 urn:oasis:names:tc:SAML:2.0:protocol',
    'GeolocationHint' => '48.827941,2.344493',
    'Type' => 'unknown',
);

?>
