<div id="newMessageWrapper">
</div>
<div id="leftHead">
	<a href="./"><img id="logo" width="250px" height="50px" src="/colorful/wtlogo_dark.png"></a>
	BETA
	<?php
	if($loggedIn){
		echo('
		<div id="userAccountBar">
			<div class="item" id="messageIndicator"><i class="indicatorIcon sprite sprite-1396343080_mail"></i><div id="mNum">...</div></div>
			<div class="item" id="postIndicator"><i class="indicatorIcon sprite sprite-1396343050_news"></i></div>
			<div class="item" id="settingsIndicator"><i class="indicatorIcon sprite sprite-1396343908_settings"></i></div>
		</div>');
	}
	?>
</div>
<div id="rightHead">
	<div id="searchWrapper">
		<div id="searchA">
			<?php 
				$preFill = (isset($query) && $query != null) ? $query : "Find something!";
				$value = (isset($query) && $query != null) ? $query : "";
			?>
			<form action='javascript:if(resultsPageActive){updatePage();}else{window.location = "/?query="+this.queryBar.value}'>
			<input type="text" id="queryBar" value="<?php echo $value ?>" placeholder="<?php echo $preFill ?>">
			<input type="submit" id="queryBarSubmit" value="Search">
			</form>
		</div>
	</div>
	<script type="text/javascript">
	</script>
</div>