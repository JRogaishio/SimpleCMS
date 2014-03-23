<?php
require_once("config.php");
require_once( CONTOLLER_PATH . "/admin.php" );
require_once( VIEW_PATH . "/siteHeader.php" );

//Create a new CMS object and load it!
$cms = new admin("admin");
$cms->connect();
$cms->load();

require_once( VIEW_PATH . "/siteFooter.php" );
?>
