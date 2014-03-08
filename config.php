<?php
session_start();
ini_set( "display_errors", true );
date_default_timezone_set( "America/New_York" );  // http://www.php.net/manual/en/timezones.php
define( "SYSTEM_VERSION", file_get_contents('version.txt'));
define( "DB_HOST", "localhost" );
define( "DB_NAME", "ferret_cms" );
define( "DB_USERNAME", "root" );
define( "DB_PASSWORD", "" );
define( "CONTOLLER_PATH", "controllers" );
define( "MODEL_PATH", "models" );
define( "VIEW_PATH", "views" );
define( "TEMPLATE_PATH", "template" );
define( "PLUGIN_PATH", "plugin" );
define( "ADMINFORM_PATH", "adminForm" );
define( "LIBRARY_PATH", "lib" );
define( "SITE_ROOT", "http://localhost/ferretCMS/" );
define( "ADMIN_USERNAME", "admin" );
define( "ADMIN_PASSWORD", "pass" );
define( "ERROR_DIR", "template/system/" );
define( "DATEFORMAT", "M, j Y" );
define( "TIMEFORMAT", "h:i:s A" );

function handleException( $exception ) {
  echo "Sorry, a problem occurred. Please try later.";
  error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );

?>

