<?php
require("config.php");
?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Ferret CMS</title>
	<link rel="stylesheet" type="text/css" href="_css/style.css" />
	
	<script type="text/javascript" src="_js/tinymce/tinymce.min.js"></script>
	<script type="text/javascript">
	tinymce.init({
		selector: "textarea",
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen",
			"insertdatetime media table contextmenu paste"
		],
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
	});
	</script>
	
</head>

<body>
	<div id="main">
	<h1>Ferret CMS!</h1>
	<?php

	$cms = new cms();
	$cms->connect();
	$cms->load("admin");

	?>
	</div>
</body>

</html>