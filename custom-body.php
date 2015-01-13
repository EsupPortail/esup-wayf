<?php // Copyright (c) 2014, Université Paris 1 Panthéon Sorbonne ?>
<body>
	<div id="wrap">

		<div class="container">

			<div class="row">
				<header id="header-logo" class="center-block"></header>
			</div>

			<div class="row text-center">
				<div class="marge-div">
					<h4 class="promptMessage"><?php echo $promptMessage ?></h4>
				</div>
			</div>

			<div class="row">
				<div id="col-co">
					<!-- Block connexion au compte paris 1 -->
					<?php if ($showFederationDiv) : ?>
						<a class="nounderline" onclick="selectMyFederation()" href="#" >
							<div id="div-co-myfederation" class="well">
								<h3><?php echo $useMyFederationAccount ?><span class="glyphicon glyphicon-arrow-right pull-right"></span></h3>
							</div>
						</a>
					<?php endif ?>
					<div class="panel panel-default">
						<a id="block-map" <?php if($isPanelFolded){ echo 'class="nounderline collapsed"';} else { echo 'class="nounderline"';} ?> data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
							<div id="div-co-wayf" class="panel-heading" role="tab" id="headingOne">
								<h3 class="panel-title"><?php echo $useOtherFederationAccount ?><span id="glyph-collapse" class="glyphicon glyphicon-chevron-down pull-right"></span></h3>
							</div>
						</a>
						<div id="collapseOne" class="panel-collapse collapse <?php if(!$isPanelFolded){ echo "in";} ?>" role="tabpanel" aria-labelledby="headingOne">
							<div class="panel-body">
								<div id="barreDeRecherche" class="input-group">
									<label class="sr-only" for="recherche"><?php echo $searchBarText; ?></label>
									<div id="search-icon" class="input-group-addon"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></div>
									<input type="text" class="form-control" id="recherche" placeholder="<?php echo $searchBarText; ?>">
								</div>

								<input name="permanent" type="hidden" value="100">


								<!-- Block liste et carte interactive -->
								<div class="row">
									<div id="conteneurListe" class="col-lg-4 well sidebar-nav">
										<ul id="listeDynamique" class="nav nav-list">

										</ul>
									</div>

									<div class="col-lg-7 hidden-xs" id="map"></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Block compte invité -->
					<?php if ($showCRUAccountDiv) : ?>
						<a class="nounderline" value="Comptes CRU" onclick="selectCRU()" href="#">
							<div id="div-co-cru" class="well">
								<p><?php echo getLocalString('cru_account') ?></p>
							</div>
						</a>
					<?php endif ?>
					<div class="checkbox">
						<label>
							<?php if ($showPermanentSetting) : ?>
								<!-- Value permanent must be a number which is equivalent to the days the cookie should be valid -->
								<input type="checkbox" name="permanent" id="rememberPermanent" value="100"><?php echo getLocalString('permanently_remember_selection') ?>
							<?php endif ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<form id="IdPList" name="IdPList" method="post" action="<?php echo $actionURL ?>">
		<div id="userInputArea">
			<div>
				<select name="user_idp" id="userIdPSelection"> 
					<option value="-" <?php echo $defaultSelected ?>><?php echo getLocalString('select_idp') ?> ...</option>
					<?php printDropDownList($IDProviders, $selectedIDP) ?>
				</select>
				<input id="form-button" type="submit" name="Select" accesskey="s" value="<?php echo getLocalString('select_button') ?>"> 
			</div>
		</div>
	</form>

	<script type="text/javascript" src="js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/leaflet.js"></script>
	<script type="text/javascript" src="js/leaflet.awesome-markers.js"></script>
	<script type="text/javascript" src="js/sprite_sheet_array.js"></script>
	<script type="text/javascript" src="js/wayf.js"></script>