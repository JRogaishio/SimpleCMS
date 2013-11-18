<?php
session_start();
ini_set( "display_errors", true );
date_default_timezone_set( "America/New_York" );  // http://www.php.net/manual/en/timezones.php
define( "DB_HOST", "localhost" );
define( "DB_NAME", "ferret_cms" );
define( "DB_USERNAME", "root" );
define( "DB_PASSWORD", "" );
define( "CLASS_PATH", "_class" );
define( "TEMPLATE_PATH", "_template" );
define( "ADMINFORM_PATH", "_adminForm" );
define( "SITE_ROOT", "localhost/ferretCMS/");
define( "ADMIN_USERNAME", "admin" );
define( "ADMIN_PASSWORD", "pass" );
require( CLASS_PATH . "/cms.php" );
define( "PAGE_NOTFOUND", "_template/system/404.php" );

function handleException( $exception ) {
  echo "Sorry, a problem occurred. Please try later.";
  error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );

?>

