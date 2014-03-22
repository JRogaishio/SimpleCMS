<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Ferret CMS</title>
	<link rel="stylesheet" type="text/css" href="<?php echo SITE_ROOT . "_css/style.css" ?>" />

</head>

<body>
	<div id="main">
	<h1>500 INTERNAL SERVER ERROR</h1>
	<h2>That's a 500 error... :(</h2>
	<br />
	<br />
	<?php echo "Count not load page \"" . $cms->get_PARENT() . "\"" . ($cms->get_CHILD() != null ? ", " . $cms->get_CHILD() . ". ": ".");?>   

	<br />
	<br />
	<a href='<?php echo SITE_ROOT; ?>'>Click here to go back to the homepage</a>
	</div>
</body>

</html>

