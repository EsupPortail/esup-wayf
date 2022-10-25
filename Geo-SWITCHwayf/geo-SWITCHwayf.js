// Copyright (c) 2014, Université Paris 1 Panthéon-Sorbonne
// geo-SWITCHwayf.js

/******************************************************************/
// Variables to configure if IDPs have no geolocation data
// This configuration focus on France
var startZoomDefault = 5;
var coordsDefault = [46.830, 3.021];

/*******************************************************************/

var select = document.getElementById('userIdPSelection');

if ($('#form-button').length){
	document.getElementById('form-button').style.display = 'none';
}

var tabIDP = {};

var knownIDP = [];

function selectMyFederation(){
	select.value = myFederationShibURL;
	$('#form-button').trigger('click');
}

function selectCRU(){
	select.value = CRUHShibURL;
	$('#form-button').trigger('click');
}

function clickList(value){
	select.value = value;
	$('#form-button').trigger('click');
}

function toggleCheckbox(element){
	var cb = document.getElementById('rememberPermanent');			
	cb.checked = !cb.checked;
}

$(function(){

	function initMap(){
		L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
			attribution: '&copy; Données cartographiques <a href="http://osm.org/copyright">OpenStreetMap</a>'
		}).addTo(map);
	};

	var normalize = function( term ) {
		var ret = "";
		for ( var i = 0; i < term.length; i++ ) {
			ret += accentMap[ term.charAt(i) ] || term.charAt(i);
		}
		return ret;
	};

	function setDefaultView(){
		var tabLatLng = [];
		
		for (var idp in tabIDP){
			if(tabIDP[idp].marker){
				tabLatLng.push(tabIDP[idp].marker.getLatLng());
			}
		}

		if (tabLatLng.length > 0){
			var bounds = new L.LatLngBounds(tabLatLng);
			map.fitBounds(bounds);
		}
		else {
			map.setView(coordsDefault, startZoomDefault);
		}
	};

	function moveToMarker(marker){
		map.panTo(tabIDP[marker].marker.getLatLng(), {animation: true, duration: 1.0, easeLinearity: 0.25});
	};

	function updateMap(){

		markerLayer.clearLayers();

		var tab = [];

		var bounds = map.getBounds();

		for (var idp in tabIDP){
			if (tabIDP[idp].marker){
				markerLayer.addLayer(tabIDP[idp].marker);
				if (bounds.contains(tabIDP[idp].marker.getLatLng())){
					tab.push(idp);
				}
			}
		}

		updateSideList(tab, "map");
	};

	function updateSideList(IDPtoDisplay, source){

		if (source === "searchBar"){
			var tabLatLng = [];
			markerLayer.clearLayers();
		}

		var div = $('#listeDynamique');
		div.html('');

		if (IDPtoDisplay.length == 0 || IDPtoDisplay.length > 1000){
			var p = $('<p/>')
			.text(IDPtoDisplay.length ? "" + IDPtoDisplay.length + ' résultats' : 'Aucun résultat.')
			.addClass('text-center')
			.css('margin-top', '15px')
			.appendTo(div);
		}

		else {

			$.each(IDPtoDisplay, function(i, idp){

				var li = $('<li/>')
				.addClass('ui-menu-item')
				.attr('role', 'menuitem')
				.appendTo(div);

				var a = $('<a/>')
				.addClass('ui-all')
				.text(idp)
				.attr('href', '#')
				.click(function(){
					clickList(tabIDP[idp].URLShibboleth);
				})
				.appendTo(li);

				var icone = $('<span/>')
				.addClass('icone ui-icone')
				.css("margin-right", "10px")
				.css("vertical-align", "sub")

				if (tabIDP[idp].logo){
					icone.css("background", "url(" + tabIDP[idp].logo + ")");
				}
				else {
					icone.css("background-position", tabIDP[idp].logoPos + "px 0px");
				}

				icone.prependTo(a);

				if (source === "searchBar" && tabIDP[idp].marker){
					tabLatLng.push(tabIDP[idp].marker.getLatLng());
					markerLayer.addLayer(tabIDP[idp].marker);
				}
			});
		}

		if (source === "searchBar" && tabLatLng.length > 0){
			map.addLayer(markerLayer);
			var bounds = new L.LatLngBounds(tabLatLng);
			map.fitBounds(bounds);
		}
	};

	function IDP(URLShibboleth, donnees){
		this.URLShibboleth = URLShibboleth;
		this.donnees = donnees;
	};

	var map = L.map('map', {
		trackResize: false
	});

	initMap();

	var markerLayer = L.markerClusterGroup({showCoverageOnHover : false, 
											maxClusterRadius : 35,
											iconCreateFunction: function(cluster){
												return new L.AwesomeMarkers.icon({
													icon: '',
													markerColor: 'blue',
													html: cluster.getChildCount()
													});												
											}
										});

	var accentMap = {
		"é" : "e",
		"è" : "e",
		"à" : "a",
		"ê" : "e",
		"ç" : "c"
	}

	var defaultMarker = L.AwesomeMarkers.icon({
		icon: '<span class="icone" style="background-position: 0px 0px;"></span>',
		markerColor: 'white'
	});

	var isSearchingWithMap = true;

	var regCROUS = /CROUS/i;
	var defaultCROUSLogo = function fetchDefaultLogoCROUS(){
		for (logo in logo_to_x) {
			if (regCROUS.test(logo)) {
				return -logo_to_x[logo]*16-16;
			}
		}
	};

	 //fetchDefaultLogoCROUS();
	
	$.each($('#userIdPSelection optgroup[id="idpList"] option'), function(i, selected){

		var nIDP;

		if (selected.getAttribute('data')){
			nIDP = new IDP(selected.value, selected.getAttribute('data'));
			tabIDP[selected.text] = nIDP;
		}
		else {
			return true;
		}

		if (selected.getAttribute('data-lat') && selected.getAttribute('data-lon')){
			tabIDP[selected.text].latitude = selected.getAttribute('data-lat');
			tabIDP[selected.text].longitude = selected.getAttribute('data-lon');
			tabIDP[selected.text].isGeo = true;
		}

		if (selected.getAttribute('logo')){
			tabIDP[selected.text].logo = selected.getAttribute('logo');
			var stringIcone = '<span class="icone" style="background: url('+ tabIDP[selected.text].logo +')"></span>';
			var awesomeMarker = L.AwesomeMarkers.icon({
				icon: stringIcone,
				markerColor: 'white'
			});
		}
		else {
			var m = tabIDP[selected.text].donnees.match(/\S+/);
			var word = m && m[0];
			var x = logo_to_x[word];
			if (x){
				tabIDP[selected.text].logoPos = -x*16-16;
			} 
			else if (regCROUS.test(selected.text)) {
				tabIDP[selected.text].logoPos = defaultCROUSLogo;
			}
		}

		if (tabIDP[selected.text].logoPos){
			var stringIcone = '<span class="icone" style="background-position: '+ tabIDP[selected.text].logoPos +'px 0px;"></span>';
			var awesomeMarker = L.AwesomeMarkers.icon({
				icon: stringIcone,
				markerColor: 'white'
			});
		}

		if (tabIDP[selected.text].isGeo) {
			if (awesomeMarker) {
				tabIDP[selected.text].marker = L.marker([tabIDP[selected.text].latitude, tabIDP[selected.text].longitude], {icon: awesomeMarker});
			}
			else {
				tabIDP[selected.text].marker = L.marker([tabIDP[selected.text].latitude, tabIDP[selected.text].longitude], {icon: defaultMarker});
			}
			markerLayer.addLayer(tabIDP[selected.text].marker);
		}

		if (tabIDP[selected.text].marker){

			var popup = L.popup({autoPan: false}).setContent(selected.text);

			tabIDP[selected.text].marker.bindPopup(popup);
			tabIDP[selected.text].marker.on('mouseover', function(){
				this.openPopup();
			});
			tabIDP[selected.text].marker.on('mouseout', function(){
				this.closePopup();
			});
			tabIDP[selected.text].marker.on('click', function(){
				clickList(tabIDP[selected.text].URLShibboleth);
			});
		}

	});
	
	var onlyMyIDP = ($('#userIdPSelection optgroup[id="idPreviousIDP"] option').length == 1 
		&& $('#userIdPSelection optgroup[id="idPreviousIDP"] option:eq(0)').val() == myFederationShibURL);
	
	if ($('#idPreviousIDP').length != 0 && !onlyMyIDP){

		var div = $('<div/>')
		.attr('id', 'divPreviousIDP');

		var h4 = $('<h4/>')
		.text($('#idPreviousIDP').attr("label") + " :");

		h4.appendTo(div);

		$.each($('#userIdPSelection optgroup[id="idPreviousIDP"] option'), function(i, value){

			if (value.value != myFederationShibURL){

				var a = $('<a/>')
				.text(value.text)
				.attr('href', '#')
				.click(function(){
					clickList(tabIDP[value.text].URLShibboleth);
					event.stopPropagation();
				})
				.hover(
					function(){
						a.css("text-decoration", "underline");
					},
					function(){
						a.css("text-decoration", "none");
					})
				.css("pointer-events","auto")
				.css("cursor","auto")
				.appendTo(div);

				var icone = $('<span/>')
				.addClass('icone ui-icone')
				.css("margin-right", "10px")
				.css("vertical-align", "sub")

				if (tabIDP[value.text].logo){
					icone.css("background", "url(" + tabIDP[value.text].logo + ")");
				}
				else {
					icone.css("background-position", tabIDP[value.text].logoPos + "px 0px");
				}

				icone.prependTo(a);

				$('<br/>').appendTo(div);
			}
		});

		if ($('#div-co-wayf h3').css('visibility') == 'hidden'){
			$('#div-co-wayf h3').css('display', 'none');
		}
		div.appendTo($('#div-co-wayf'));
	}

	markerLayer.addTo(map);

	if ($('.collapsed').length == 0){
		if ($('#map').css('display') == 'none'){
			updateSideList(Object.keys(tabIDP), "map");
			isSearchingWithMap = false;
		}
		else {
			setDefaultView();
			updateSideList(Object.keys(tabIDP), "map");
		}
	}

	map.on('mousedown moveend', function(e) {
		if (e.type === 'mousedown'){
			isSearchingWithMap = true;
		}
		else {
			if (isSearchingWithMap){
				updateMap();
			}
		}
	});
	
	$('#listeDynamique')
	.mouseover(function(event){
		if (tabIDP[event.target.text] && tabIDP[event.target.text].marker){
			tabIDP[event.target.text].marker.openPopup();
		}
	})
	.mouseout(function(event){
		if (tabIDP[event.target.text] && tabIDP[event.target.text].marker){
			tabIDP[event.target.text].marker.closePopup();
		}
	});


	$('#collapseOne').on('shown.bs.collapse show.bs.collapse hide.bs.collapse', function(e){
		switch(e.type){
		case 'shown':
			if ($('#map').css('display') == 'none'){
				updateSideList(Object.keys(tabIDP), "map");
				isSearchingWithMap = false;
			}
			else {
				setDefaultView();
			}
			break;
		case 'show':
			$('#glyph-collapse').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
			break;
		case 'hide':
			$('#glyph-collapse').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
			break;
		}
	});
	
	$("#recherche")
	.focus(function() {
		if (this.value == ''){
			isSearchingWithMap = false;
			updateSideList(Object.keys(tabIDP), "searchBar");
		}
	})
	
	.autocomplete({
		minLength: 3,
		source: function( request, response ) {
			isSearchingWithMap = false;
			var tabMatcher = [];
			var tabRequete = request.term.split(/ /);
			for (var iteration in tabRequete){
				tabMatcher.push(new RegExp($.ui.autocomplete.escapeRegex(normalize(tabRequete[iteration])), "i" ));
			}

			response( updateSideList($.grep( Object.keys(tabIDP), function( text ) {

				var textSplited = text.split(/-| |'/);
				console.log(textSplited)
				for (var matcher in tabMatcher){
					var hasMatched = false;
					for (var word in textSplited){
						if (tabMatcher[matcher].test(normalize(textSplited[word]))){
							hasMatched = true;
						}
					}
					if (hasMatched === false){
						return false;
					}	
				}
				return true;
			}), "searchBar"))
		}
	});
});