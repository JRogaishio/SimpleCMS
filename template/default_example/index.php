<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $this->getScope("page")->getTitle(); ?></title>
<link rel="stylesheet" type="text/css"
	href="<?php echo SITE_ROOT . TEMPLATE_PATH . "/" . $page->getTemplatePath() . "/main.css";?>" />

</head>

<body>
	<div id="main">
	<?php $this->load_navigation(array("home","blog","archive")); ?>
	
	<h1><?php echo $this->getScope("page")->getTitle(); ?></h1>
	<?php
	
	$this->getScope ( "page" )->display_posts ( 5, true );
	
	/*
	//Plugin Loading Example
	if($this->getScope("page")->hasFlag("board")) {
		$this->getScope("board")->loadBoard();
	}*/
	
	
	?>
	</div>
</body>

</html>

