# esup-wayf documentation

## To install the software SWITCHwayf

- Documentation SWITCH : Refer to the file README
- Documentation Renater : <https://services.renater.fr/federation/documentation/guides-installation/sp3/chap06>

## Synopsis

esup-wayf is based on the software SWITCHwayf v1.19.4. It is a custom theme that adds a map and locates the different identity providers on it. The user can select an IDP with the map or with a search field. It is possible to use Shibboleth's discofeed to filter the allowed IDP.
![esup-wayf](https://github.com/EsupPortail/esup-wayf/blob/master/images/wayf.png)

## Dependencies

In order to run the WAYF and the CRON, you need the following softwares installed on your server: PHP5, Python, xmllint and imagick

## Installation

- Follow the instructions given by SWITCH to install SWITCHwayf v1.19.4
- In config.php, uncomment and configure the Geo-SWITCHwayf variables.
- You can adapt the colors and style in the file Geo-SWITCHwayf/css/style.css
- If your federation is not renater, renater-test, edugain or edugain-test, you need to adapt the script "update-wayf.sh" to fetch the right metadata files.
- Run the script `Geo-SWITCHwayf/update.sh {federation_name}`. It will update idp's data, geolocation and icons.
- Configure a crontab to run this command daily.

To enable discofeed on SP (Require Shibboleth >= 2.4)

- In the service provider's shibboleth2.xml enable the discofeed (make sure that the location is "/DiscoFeed"). example: `<Handler type="DiscoveryFeed" Location="/DiscoFeed"/>`
- Restart Shibboleth.
- Check that you can display the JSON file at "yourSP.univ.fr/Shibboleth.sso/DiscoFeed", the content of the SP's metadata file should appear.

## Geolocation sources and priority

Geolocation coordinates displayed on the map are resolved from up to three sources,
tried in the following order for each IdP:

1. **`mdui:GeolocationHint`** already declared in the SAML federation metadata  
   (most authoritative when present — set by the IdP operator in the metadata).

2. **eduroam CAT API** (`https://cat.eduroam.org`)  
   The Configuration Assistant Tool maintains coordinates for eduroam-enabled
   institutions. Matching is performed by domain name using three successive
   strategies (first match wins):
   - `mdui:DomainHint` declared in the IdP's MDUI extensions
   - `shibmd:Scope` values declared in the IdP's metadata extensions
   - Hostname derived from the entityID URL, with progressive subdomain stripping  
     (e.g. `idp.univ-paris1.fr` → `univ-paris1.fr`)

3. **Discojuice feed** (`$UseDiscojuiceGeolocation`)  
   JSON files downloaded by `Geo-SWITCHwayf/discojuice/update-discojuice.sh`.

To enable the CAT eduroam enrichment, set in your `config.php`:

```php
$UseCatEduroamGeolocation = true;
```

This requires network access to `https://cat.eduroam.org/` during the metadata
refresh (cron `update.sh`). CAT data is only used as a fallback for IdPs that
do not already have a `GeolocationHint` in the federation metadata.

**Important:**

Make sure to enable writing permissions for the files IDProvider.metadata.php, SProvider.metadata.php, wayf_metadata.lock.

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
