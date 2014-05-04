<?php
/* App metatag
 * #AppName: %APPLICATION_NAME%#
 * 
 */

//application setup


define('APPLICATION', '%APPLICATION_NAME%'); //REQUIRED : Define application name
// define('APPLICATION_DIR', '%APPLICATION_DIR%'); // OPTIONAL : Path to the application dir, default ROOT_DIR/APPLICATION_NAME
// define(WEB_DIR', '%WEB_DIR%');   //OPTIONAL : Path to the web directory, default ROOT_DIR/web

// define('MODULE_DEFAUT', 'main');  //OPTIONAL : Default application module, default 'main'
// define('ACTION_DEFAUT', 'index'); //OPTIONAL : Default action for modules, default 'index'
define('DEBUG', true); //OPTIONAL : Define debug flag, default false
define('MODE_INSTALL', true); //OPTIONAL : Set the application in installation mode, default false.
define('NO_LOGIN', true); //OPTIONAL : Set the application in no credentials mode, default false
define('SUPER_ADMIN', true); //OPTIONAL : Set user as administrator if NO_LOGIN mose set to true, default true
//Mihimana bootstrap
require_once '%BOOTSTRAP_SCRIPT_PATH%'; // generaly '../lib/mihimana/mihimana.php';
