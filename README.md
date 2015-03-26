esup-wayf documentation

## To install the software SWITCHwayf :

- Documentation SWITCH : Refer to the file README
- Documentation Renater : https://services.renater.fr/federation/docs/installation/sp_decembre2012#installation_d_un_service_de_decouverte


## Synopsis

esup-wayf is based on the software SWITCHwayf v1.19.4. It is a custom theme that adds a map and locates the different identity providers on it. The user can select an IDP with the map or with a search field. It is possible to use Shibboleth's discofeed to filter the allowed IDP.


## Installation

- Follow the instructions given by SWITCH to install SWITCHwayf v1.19.4
- In config.php, configure the esup-wayf variables.
- Configure variables "myIDP" and "cru" in the file Geo-SWITCHwayf/geo-SWITCHwayf.js (Values need to be the same as those delivered by Shibboleth). You can also set the variable "isGeolocationEnabled" to true if you want the map to be set at the user's position.
- You can adapt the colors and style in the file Geo-SWITCHwayf/css/style.css
- If your federation is not Renater, you need to adapt the script "update-wayf.sh" to fetch the right metadata files.
- Run the script `Geo-SWITCHwayf/update.sh WAYF-PATH renater` . It will update idp's data, geolocation and icons (Run this script daily in a CRON). 

To enable discofeed filter (Require Shibboleth >= 2.4)

- In the service provider's shibboleth2.xml enable the discofeed (make sure that the location is "/DiscoFeed") and add a whitelist which contains all the allowed IDP (these IDP must exist in the federation XML file).
- Restart shibboleth
- Check that you can display the JSON file at "yourSP.univ.fr/shibboleth-path/DiscoFeed"

Important :

Make sure to enable writing permissions for the files discofeed.metadata.php, IDProvider.metadata.php, SProvider.metadata.php and wayf_metadata.lock

Other softwares and library used

	SWITCH WAYF v1.19.4
	Leaflet v0.7.3
	Open Street Map
	Bootstrap v3.3.1
	JQuery v1.10.2
	Awesome Markers for Leaflet v1.0
	Leaflet.markercluster
	Renater Favicon-Fetcher
	ImageMagick