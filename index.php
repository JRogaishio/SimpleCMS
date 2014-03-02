<?php
require_once("config.php");
require_once( CONTOLLER_PATH . "/pub.php" );

$cms = new pub("public");
$cms->connect();
$cms->load();
?>
