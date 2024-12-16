<?php // Copyright (c) 2014, SWITCH ?>

<?php if (isValidShibRequest()) : 
// Send automatically the form if the user has selected the "remember idp" checkbox ?>
	<form class="vcenter" id="IdPList" name="IdPList" method="post" action="<?php echo $actionURL ?>">
		<input id="createCookie" style="margin: 30px;" class="btn btn-default" type="submit" accesskey="s" name="Select" name="permanent" value="<?php echo getLocalString('goto_sp') ?>">
		<select name="permanent_user_idp" id="userIdPSelection">
			<option value="<?php echo $permanentUserIdP ?>" logo="<?php echo $permanentUserIdPLogo ?>"><?php echo $permanentUserIdPName ?></option>
		</select>
	</form>
	<script type="text/javascript">
		$(function(){
			document.getElementById('IdPList').style.display = 'none';
			document.getElementById('footer').style.display = 'none';
			$('#createCookie').click();
		});
	</script>

<?php else: ?>

<body>
	<div id="wrap">
		<div class="container">
			<div class="row">
				<header id="header-logo" class="center-block"></header>
			</div>
			<div class="row">
				<form class="vcenter" id="IdPList" name="IdPList" method="post" action="<?php echo $actionURL ?>">
					<div class="text-center" id="userInputArea">
						<h4 style="margin: 30px;" id="entete"><?php echo getLocalString('settings') . " : "; ?></h4>
						<input style="margin: 30px;" class="btn btn-default" type="submit" accesskey="c" name="clear_user_idp" value="<?php echo getLocalString('delete_permanent_cookie_button') ?>">
						<div class="text-center" style="margin: 30px;">
						<p class="text-muted promptMessage"><?php echo getLocalString('permanent_cookie_notice'); ?></p>
						</div>
						<select name="permanent_user_idp" id="userIdPSelection">
							<option value="<?php echo $permanentUserIdP ?>" logo="<?php echo $permanentUserIdPLogo ?>"><?php echo $permanentUserIdPName ?></option>
						</select>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		var defaultIDP = $('#userIdPSelection option:selected').text();
		$('#entete').append(defaultIDP);
		$('.promptMessage').append(defaultIDP);
		document.getElementById('userIdPSelection').style.display = 'none';
	</script>

<?php endif ?>