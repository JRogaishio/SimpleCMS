<div class="cms_top">
			<h1 class="cms_title"><a href="admin.php">{f}</a></h1>
			<div class="cms_topItems">
				<form action='admin.php' method='get'>
				<input type="hidden" name="type" value="search" />
				<input type="text" name="action" value="search here" size="25" class="cms_searchBox" onclick="if($(this).val()=='search here'?$(this).val(''):$(this).val());"/>
		</form>
		<a href="admin.php?type=web_state&action=logout"><span id="cms_login">Log out</span></a> <br />
	</div>	
</div>
<div class="cms_topSpacer">&nbsp;</div>