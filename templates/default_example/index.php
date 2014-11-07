<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $this->getScope("pageService")->getPage()->getTitle(); ?></title>
<link rel="stylesheet" type="text/css"
	href="<?php echo SITE_ROOT . TEMPLATE_PATH . "/" . $this->getScope("pageService")->getPage()->getTemplatePath() . "/main.css";?>" />

</head>

<body>
	<div id="main">
	<?php $this->load_navigation(array("home","blog","archive")); ?>
	
	<h1><?php echo $this->getScope("pageService")->getPage()->getTitle(); ?></h1>
	<?php
	//Return Pre-formatted posts
	//$this->getScope("pageService")->display_posts ( 5, true );
	
	//Get post objects in array form
	$posts = $this->getScope("pageService")->get_posts ( 5 );

	foreach($posts as $post) {
		echo $post->getTitle() . "<br />";
	}
	
	//Key exammple
	//echo $this->getScope("keyService")->getValue('KeyName');
	
	/*
	//Plugin Loading Example
	if($this->getScope("page")->hasFlag("board")) {
		$this->getScope("board")->loadBoard();
	}*/
	
	//$this->getScope("example")->load();
	
	
	?>
	</div>
</body>

</html>

