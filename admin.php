<?php
require_once("config.php");
require_once( CONTOLLER_PATH . "/admin.php" );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta charset="UTF-8">
	<title>Ferret CMS</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="FerretCMS Administrator">
	<meta name="keywords" content="">	
	
	<!--Twitter Bootstrap-->
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
	<script src="js/bootstrap.min.js"></script>
	
	<link rel="stylesheet" type="text/css" href="css/reset.css" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<script type="text/javascript" src="js/url.js"></script>
	<script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
	<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
	
	<script type="text/javascript">
	tinymce.init({
		selector: "textarea",
		width : 700,
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen",
			"insertdatetime media table contextmenu paste"
		],
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
	});
	
	//Navigation accordion
	$(function () {
		$(".cms_navItemList").hide();
		var type = getUrlVars()["type"];
		
		//Auto-open the active navigation menu
		if(type != undefined) {
			type = type.replace("#", "");
			
			if(type.indexOf("Display") > 1)
				type = type.replace("Display", "");
				
			$("#cms_navItemList_" + type).show();
		}
		
		$(".cms_navItemTitle").click(function(){
			$(this).parent().children(".cms_navItemList").slideToggle(200);
		});
	});
	
	
	</script>
	
</head>
<body>
	<?php
	//Create a new CMS object and load it!
	$cms = new admin("admin");
	$cms->connect();
	$cms->load();

	?>
</body>
</html>
