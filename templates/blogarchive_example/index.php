<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $this->getScope("pageService")->getPage()->getTitle(); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_ROOT . TEMPLATE_PATH . "/" . $this->getScope("pageService")->getPage()->getTemplatePath() . "/main.css";?>" />
	
</head>

<body>
	<div id="main">
	<?php $cms->load_navigation(array("home","blog","archive")); ?>
	
	<h1><?php echo $this->getScope("pageService")->getPage()->getTitle(); ?></h1>
	<?php

	
	$this->getScope("pageService")->display_posts(-1, true, false, true, $cms->get_CHILD(), "blog");
	
	?>
	</div>
</body>

</html>

