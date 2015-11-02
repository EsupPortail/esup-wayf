<?php // Copyright (c) 2014, SWITCH ?>
<body>
	<div id="wrap">

		<div class="container">
			<?php if ($isUpdatingDiscoFeed) : ?>
				<div class="alert alert-info" role="alert" style="margin-top:20px;">
					<h4><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span><?php echo " ".$updateDiscofeedTitle; ?></h4>
					<p><?php echo $updateDiscofeed1; ?><a href="<?php echo $SPShibUrl; ?>"><?php echo $SPShibUrl; ?></a></p>
					<p><?php echo $updateDiscofeed2; ?></p>
				</div>
			<?php endif ?>

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
					<?php if ($showLocalIDPDiv) : ?>
						<a class="nounderline" onclick="selectMyFederation()" href="#" >
							<div id="div-co-myfederation" class="well">
								<h3><?php echo $useMyFederationAccount; ?><span class="glyphicon glyphicon-arrow-right pull-right"></span></h3>
							</div>
						</a>
					<?php endif ?>
					<div class="panel panel-default">
						<?php if ($isPanelFolded) : ?>
							<a id="block-map" class="nounderline collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
						<?php else: ?>
							<a id="block-map" class="nounderline" data-toggle="collapse" data-parent="#accordion" <?php if($adaptPanelText){echo 'href="#noCollapse" style="pointer-events: none; cursor: default;"';}else{echo 'href="#collapseOne"';} ?> aria-expanded="true" aria-controls="collapseOne">
						<?php endif ?>
							<div id="div-co-wayf" class="panel-heading" role="tab" id="headingOne" <?php if($adaptPanelText){echo 'style="background-color: #F5F5F5;"';} ?>>
									<h3 class="panel-title"<?php if($adaptPanelText){echo 'style="visibility: hidden;"';} ?>><?php echo $useOtherFederationAccount ?><span id="glyph-collapse" class="glyphicon glyphicon-chevron-down pull-right"></span></h3>
							</div>
						</a>
						<?php if ($isPanelFolded) : ?>
							<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
						<?php else: ?>
							<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
						<?php endif ?>
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
								<input type="checkbox" onchange="toggleCheckbox(this)"><?php echo getLocalString('permanently_remember_selection') ?>
							<?php endif ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>

	<form id="IdPList" name="IdPList" method="post" action="<?php echo $actionURL ?>" style="display:none;">
		<div id="userInputArea">
			<div>
				<select name="user_idp" id="userIdPSelection"> 
					<option value="-" <?php echo $defaultSelected ?>><?php echo getLocalString('select_idp') ?> ...</option>
					<?php printDropDownList($IDProviders, $selectedIDP) ?>
				</select>
				<input type="checkbox" name="permanent" id="rememberPermanent" value="100"><?php echo getLocalString('permanently_remember_selection') ?>
				<input id="form-button" type="submit" name="Select" accesskey="s" value="<?php echo getLocalString('select_button') ?>"> 
			</div>
		</div>
	</form>

	<script type="text/javascript">
		var myFederationShibURL = "<?php echo $LocalIDPID; ?>";
		var CRUHShibURL = "<?php echo $CRUID; ?>";
	</script>

	<script type="text/javascript" src="Geo-SWITCHwayf/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/js/leaflet.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/js/leaflet.awesome-markers.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/js/leaflet.markercluster.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/js/sprite_sheet_array.js"></script>
	<script type="text/javascript" src="Geo-SWITCHwayf/geo-SWITCHwayf.js"></script>