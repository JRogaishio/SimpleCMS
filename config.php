<?php
session_start();
ini_set( "display_errors", true );
date_default_timezone_set( "America/New_York" );  // http://www.php.net/manual/en/timezones.php
define( "DB_HOST", "localhost" );
define( "DB_NAME", "ferret_cms" );
define( "DB_USERNAME", "root" );
define( "DB_PASSWORD", "" );
define( "CLASS_PATH", "class" );
define( "MODEL_PATH", "model" );
define( "VIEW_PATH", "view" );
define( "TEMPLATE_PATH", "template" );
define( "ADMINFORM_PATH", "adminForm" );
define( "SITE_ROOT", "http://localhost/ferretCMS/");
define( "ADMIN_USERNAME", "admin" );
define( "ADMIN_PASSWORD", "pass" );
require( CLASS_PATH . "/cms.php" );
define( "ERROR_DIR", "_template/system/" );
define( "DATEFORMAT", "M, j Y" );
define( "TIMEFORMAT", "h:i:s A" );

function handleException( $exception ) {
  echo "Sorry, a problem occurred. Please try later.";
  error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );

?>

