// Copyright (c) 2014, Université Paris 1 Panthéon-Sorbonne

// Récupération du select qui contient les données
var select = document.getElementById('userIdPSelection');

// On masque le formulaire natif de SWITCH WAYF
select.style.display = 'none';

if ($('#form-button').length){
	document.getElementById('form-button').style.display = 'none';
}

// Nom de la fédération telle qu'elle apparait dans la liste des idp
var myIDP = "Université Paris 1 Panthéon-Sorbonne TEST";

// Nom pour les comptes invités, tels qu'ils apparaissent dans la liste des idp
var cru = "Comptes CRU";

// Structure de donnée pour conserver les infos de chaque établissement
var tabIDP = {};

// Tableau pour les IDP qui ont déjà été visité par l'utilisateur
var knownIDP = [];

// Fonction qui se déclenche quand l'utilisateur sélectionne directement Paris 1
function selectMyFederation(){
	var myFederation =  tabIDP[myIDP];
	select.value = myFederation.URLShibboleth;
	$('#form-button').trigger('click');
}

// Fonction qui se déclenche quand l'utilisateur sélectionne les comptes CRU
function selectCRU(){
	var CRUAccounts = tabIDP[cru];
	select.value = CRUAccounts.URLShibboleth;
	$('#form-button').trigger('click');
}

// Fonction qui se déclenche quand on clique sur un item de la liste dynamique
function clickList(value){
	select.value = value;
	$('#form-button').trigger('click');
}

$(function(){

	function initMap(){
		// Adresse pour la carte sans passer par proxy
		//http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png
		L.tileLayer('map/{s}/{z}/{x}/{y}', {
			attribution: '&copy; Données cartographiques <a href="http://osm.org/copyright">OpenStreetMap</a>'
		}).addTo(map);
	};

	// Fonction qui permet de gérer les pbm d'accents dans la barre de recherche
	var normalize = function( term ) {
		var ret = "";
		for ( var i = 0; i < term.length; i++ ) {
			ret += accentMap[ term.charAt(i) ] || term.charAt(i);
		}
		return ret;
	};

	function successCallback(position){
		map.setView([position.coords.latitude, position.coords.longitude], startZoomGeo);
	};  

	function errorCallback(error){
		setDefaultView();
	};

	function setDefaultView(){
		map.setView(coordsDefault, startZoomDefault);
	};

	function moveToMarker(marker){
		map.panTo(tabIDP[marker].marker.getLatLng(), {animation: true, duration: 1.0, easeLinearity: 0.25});
	};

	// 10 fois plus rapide que de faire tab = [];
	function resetTab(tab){
		while (tab.length){
			tab.pop();
		}
	};

	// Mise à jour de la liste par la carte
	function updateDivGeo(){

		map.removeLayer(searchMarkers);
		map.addLayer(allMarkers);

		var tab = [];

		var bounds = map.getBounds();

		for (var iteration in tabRecherche){
			if (tabIDP[tabRecherche[iteration]] && tabIDP[tabRecherche[iteration]].URLShibboleth && tabIDP[tabRecherche[iteration]].marker){
				if (bounds.contains(tabIDP[tabRecherche[iteration]].marker.getLatLng())){
					tab.push(tabRecherche[iteration]);
				}
			}
		}

		refreshListeDynamique(tab);
	};

	// Mise à jour de la liste par la barre de recherche
	function updateDivSearchBar(tab){

		tabLatLng = [];

		map.removeLayer(allMarkers);
		searchMarkers.clearLayers();

		var div = $('#listeDynamique');
		div.html('');

		if (tab.length == 0){
			var p = $('<p/>')
			.text('Aucun résultat.')
			.addClass('text-center')
			.css('margin-top', '15px')
			.appendTo(div);
		}

		else {

			$.each(tab, function(iteration){

				if (tabIDP[tab[iteration]] && tabIDP[tab[iteration]].URLShibboleth){

					var li = $('<li/>')
					.addClass('ui-menu-item')
					.attr('role', 'menuitem')
					.appendTo(div);

					var a = $('<a/>')
					.addClass('ui-all')
					.text(tab[iteration])
					.attr('href', '#')
					.click(function(){
						clickList(tabIDP[tab[iteration]].URLShibboleth);
					})
					.appendTo(li);

					var icone = $('<span/>')
					.addClass('icone ui-icone')
					.css("background-position", tabIDP[tab[iteration]].logo + "px 0px")
					.css("margin-right", "10px")
					.css("vertical-align", "sub")
					.prependTo(a);
				}

				if (tabIDP[tab[iteration]] && tabIDP[tab[iteration]].URLShibboleth && tabIDP[tab[iteration]].marker){
					tabLatLng.push(tabIDP[tab[iteration]].marker.getLatLng());
					searchMarkers.addLayer(tabIDP[tab[iteration]].marker);
				}
			});
		}

		if (tabLatLng.length > 0 && !isSearchingWithMap){
			map.addLayer(searchMarkers);
			var bounds = new L.LatLngBounds(tabLatLng);
			map.fitBounds(bounds);
		}

	};

	// Crée la liste d'établissements avec les données du tableau passé en paramètre
	function refreshListeDynamique(tab){

		var div = $('#listeDynamique');
		div.html('');

		if (tab.length == 0){
			var p = $('<p/>')
			.text('Aucun résultat.')
			.addClass('text-center')
			.css('margin-top', '15px')
			.appendTo(div);
		}

		else {

			$.each(tab, function(iteration){

				var li = $('<li/>')
				.addClass('ui-menu-item')
				.attr('role', 'menuitem')
				.appendTo(div);

				var a = $('<a/>')
				.addClass('ui-all')
				.text(tab[iteration])
				.attr('href', '#')
				.click(function(){
					clickList(tabIDP[tab[iteration]].URLShibboleth);
				})
				.appendTo(li);

				var icone = $('<span/>')
				.addClass('icone ui-icone')
				.css("background-position", tabIDP[tab[iteration]].logo + "px 0px")
				.css("margin-right", "10px")
				.css("vertical-align", "sub")
				.prependTo(a);

			});
		}
	};

	function IDP(URLShibboleth, donnees){
		this.URLShibboleth = URLShibboleth;
		this.donnees = donnees;
	};

	function IDP(URLShibboleth, donnees, latitude, longitude){
		this.URLShibboleth = URLShibboleth;
		this.donnees = donnees;
		this.latitude = latitude;
		this.longitude = longitude;
	};

	var map = L.map('map');

	initMap();

	// Tableau qui conserve les clefs pour accéder aux objets de tabIDP
	var tabRecherche = [];

	// Groupe qui contient tout les markers
	var allMarkers = L.layerGroup();

	// Groupe qui contient seulement les markers recherchés dans la barre
	var searchMarkers = L.layerGroup();

	var isPositionSet = false;

	// La recherche ne prend pas en compte les accents.
	var accentMap = {
		"é" : "e",
		"è" : "e",
		"à" : "a",
		"ê" : "a",
		"ç" : "c"
	}

	// Icone de Renater par défaut pour les établissements qui n'ont pas de favicon
	var defaultMarker = L.AwesomeMarkers.icon({
		icon: '<span class="icone" style="background-position: 0px 0px;"></span>',
		markerColor: 'white'
	});

	var startZoomGeo = 12;

	// valeurs à utiliser si la géolocalisation ne fonctionne pas
	var startZoomDefault = 5;
	var coordsDefault = [46.830, 3.021];

	// Variable qui détermine si l'utilisateur interagit avec la carte ou avec la barre de recherche
	var isSearchingWithMap = true;

	// Récupération des infos du select
	$.each($('#userIdPSelection optgroup[id="idpList"] option'), function(i, selected){

		var nIDP;
		
		if (selected.getAttribute('data-lat') && selected.getAttribute('data-lon') && selected.getAttribute('data')){
			nIDP = new IDP(selected.value, selected.getAttribute('data'), selected.getAttribute('data-lat'),
				selected.getAttribute('data-lon'));
			tabIDP[selected.text] = nIDP;
			tabRecherche.push(selected.text);
		}
		else if (selected.getAttribute('data')){
			nIDP = new IDP(selected.value, selected.getAttribute('data'));
			tabIDP[selected.text] = nIDP;
			tabRecherche.push(selected.text);
		}

		// Récupération des logos
		if (tabIDP[selected.text] && tabIDP[selected.text].donnees){
			for (var iteration in logos){
				var dataSplit = tabIDP[selected.text].donnees.split('\ ');
				if (dataSplit[0] == logos[iteration]){
					tabIDP[selected.text].logo = -iteration*16-16;
				}
			}
		}

		// Placement des marqueurs sur la carte
		// On vérifie que les objets on toutes les infos nécessaires pour être placé sur la carte
		if (tabIDP[selected.text] && tabIDP[selected.text].latitude
			&& tabIDP[selected.text].longitude && tabIDP[selected.text].logo){
			var stringIcone = '<span class="icone" style="background-position: '+ tabIDP[selected.text].logo +'px 0px;"></span>';
		var awesomeMarker = L.AwesomeMarkers.icon({
			icon: stringIcone,
			markerColor: 'white'
		});
		tabIDP[selected.text].marker = L.marker([tabIDP[selected.text].latitude, tabIDP[selected.text].longitude], {icon: awesomeMarker});
		allMarkers.addLayer(tabIDP[selected.text].marker);
	}

	else if (tabIDP[selected.text]){
		if (tabIDP[selected.text].logo){
			var stringIcone = '<span class="icone" style="background-position: '+ tabIDP[selected.text].logo +'px 0px;"></span>';
			var awesomeMarker = L.AwesomeMarkers.icon({
				icon: stringIcone,
				markerColor: 'white'
			});
		}
		else {
				// Logo de renater pour les établissements qui n'ont pas de logo
				tabIDP[selected.text].logo = 0;
			}

			if (tabIDP[selected.text].latitude && tabIDP[selected.text].longitude){
				tabIDP[selected.text].marker = L.marker([tabIDP[selected.text].latitude, tabIDP[selected.text].longitude], {icon: defaultMarker});
				allMarkers.addLayer(tabIDP[selected.text].marker);
			}
		}

		// Configuration des events et des popups pour les marqueurs
		if (tabIDP[selected.text] && tabIDP[selected.text].marker){

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

	// Récupération des idp déja utilisés si il y'en a
	if ($('#idPreviousIDP').length != 0){

		var div = $('<div/>')
		.attr('id', 'divPreviousIDP');

		var h4 = $('<h4/>')
		.text($('#idPreviousIDP').attr("label") + " :");

		h4.appendTo(div);
		$.each($('#userIdPSelection optgroup[id="idPreviousIDP"] option'), function(i, value){

			if (value.text != myIDP){

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
				.appendTo(div);

				var icone = $('<span/>')
				.addClass('icone ui-icone')
				.css("background-position", tabIDP[value.text].logo + "px 0px")
				.css("margin-right", "10px")
				.css("vertical-align", "sub")
				.prependTo(a);

				icone.prependTo(a);

				$('<br/>').appendTo(div);
			}
		});

		div.appendTo($('#div-co-wayf'));
	}

	allMarkers.addTo(map);

	// Si la carte est dépliée au chargement de la page, on affiche les infos
	if ($('.collapsed').length == 0){
		if ($('#map').css('display') == 'none'){
			refreshListeDynamique(tabRecherche);
		}
		else {
			setDefaultView();

			if (navigator.geolocation){
				navigator.geolocation.getCurrentPosition(successCallback, errorCallback);

			isPositionSet = true;
			}
		}
	}

	// Actualisation des informations dans le div quand l'utilisateur déplace la carte
	map.on('moveend', function() {
		if (isSearchingWithMap){
			updateDivGeo();
		}
	});

	
	map.on('mousedown', function(){
		isSearchingWithMap = true;
	});


	// Ouverture d'un popup quand mouseover dans la liste
	$('#listeDynamique').mouseover(function(event){
		if (tabIDP[event.target.text] && tabIDP[event.target.text].marker){
			tabIDP[event.target.text].marker.openPopup();
		}
	});

	$('#collapseOne').on('hide.bs.collapse', function () {
		$('#glyph-collapse').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
	});

	$('#collapseOne').on('show.bs.collapse', function () {
		$('#glyph-collapse').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
	});

	// Il faut attendre la fin de la transition collapse pour placer la vue
	$('#collapseOne').on('shown.bs.collapse', function () {
		// Si la carte n'est pas visible (sur téléphone par exemple)
		if ($('#map').css('display') == 'none'){
			refreshListeDynamique(tabRecherche);
			isSearchingWithMap = false;
		}
		else if (!isPositionSet) {

			setDefaultView();

			if (navigator.geolocation){
				navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
			}
			isPositionSet = true;

		}
	});

	// Si la barre de recherche est vide, on affiche toute les propositions
	$("#recherche").focus(function() {
		if (this.value == ''){
			isSearchingWithMap = false;
			updateDivSearchBar(tabRecherche);
		}
	});

	$(window).resize(function(){
		if ($('#map').css('display') == 'none'){
			refreshListeDynamique(tabRecherche);
			isSearchingWithMap = false;
		}
		// else if (isPositionSet && $('#map').css('display') == 'none') {
		// 	console.log("caca");
		// 	if (navigator.geolocation){
		// 		navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
		// 	}
		// 	else {
		// 		setDefaultView();
		// 	}
		// }
	});

	// Fermeture d'un popup quand la souri quitte le li
	$('#listeDynamique').mouseout(function(event){
		if (tabIDP[event.target.text] && tabIDP[event.target.text].marker){
			tabIDP[event.target.text].marker.closePopup();
		}
	});

	// Gere l'actulisation de la carte et de la liste quand on fait une recherche
	$( "#recherche" ).autocomplete({
		minLength: 0,
		source: function( request, response ) {
			isSearchingWithMap = false;
			var tabMatcher = [];
			var tabRequete = request.term.split('\ ');

			for (var iteration in tabRequete){
				tabMatcher.push(new RegExp( $.ui.autocomplete.escapeRegex(normalize(tabRequete[iteration])), "i" ));
			}

			response( updateDivSearchBar($.grep( tabRecherche, function( text ) {
				// Recherche par mot et pas par substring
				var correct = true;
				$.each(tabMatcher, function(iteration){
					if (!tabMatcher[iteration].test(normalize(text))){
						correct = false;
					}
				});
				return correct && tabIDP[text];
			}

			)))}
		});

});

