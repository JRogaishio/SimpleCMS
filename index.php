<?php
require("config.php");
	
$cms = new cms();
$cms->connect();
$cms->load("user");
?>