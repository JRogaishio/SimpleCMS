<?php
session_start();
ini_set( "display_errors", true );

//SITE CONFIGURATIONS
date_default_timezone_set( "America/New_York" );  // http://www.php.net/manual/en/timezones.php
define( "DB_HOST", "localhost" ); 	//Database host
define( "DB_NAME", "ferret_cms" ); 	//Database username
define( "DB_USERNAME", "root" ); 	//Database username
define( "DB_PASSWORD", "" );		//Database password
define( "SITE_ROOT", "http://ferretcms.localhost/" ); //This should be http://mywebsite.com/
define( "DATEFORMAT", "M, j Y" );
define( "TIMEFORMAT", "h:i:s A" );

//SYSTEM  BELOW - DO NOT EDIT
define( "SYSTEM_VERSION", file_get_contents('version.txt'));
define( "CONTOLLER_PATH", "controllers" );
define( "MODEL_PATH", "models" );
define( "VIEW_PATH", "views" );
define( "TEMPLATE_PATH", "templates" );
define( "PLUGIN_PATH", "plugins" );
define( "ADMINFORM_PATH", "adminForm" );
define( "LIBRARY_PATH", "lib" );
define( "ERROR_DIR", "templates/system/" );

function handleException( $exception ) {
  echo "Sorry, a problem occurred. Please try later.";
  error_log( $exception->getMessage() );
}

set_exception_handler( 'handleException' );

?>

