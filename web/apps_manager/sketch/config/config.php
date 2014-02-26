<?php
/*
 * Constante redefinnissable a ce niveau pour la personnalisation
 */
//define('BASE_DIR', dirname(dirname(dirname(__FILE__)))); //Racine
//define('WEB_DIR', BASE_DIR.'/web');
//define('APPLICATION_DIR', BASE_DIR.'/'.APPLICATION);
//define('LIB_DIR', BASE_DIR.'/lib');
//define('MODELS_DIR', LIB_DIR.'/models');
//define('MIGRATION_DIR', LIB_DIR.'/migration');
//define('FIXTURE_DIR', LIB_DIR.'/fixtures');
//define('MAIDES_DIR', LIB_DIR.'/maides');
//define('DOCTRINE_DIR', LIB_DIR.'/Doctrine-1.2.4');
//define('CONFIG_DIR', LIB_DIR.'/config');
//define('SQLITE_DIR', LIB_DIR.'/sqlite');

/*
 * Database configuration
 */
define('DATABASE', '%database%');

/*
 * Login/register configuration
 */
define ('LOGIN_MODE', LOGIN_BY_USER); //User identhified by username
define ('REGISTER_MODE', REGISTER_BY_USER); //Registration use username

?>
