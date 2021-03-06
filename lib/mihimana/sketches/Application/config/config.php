<?php
/*
 * DB Definition
 */
define('DATABASE', 'mysql://%DB_USER%:%DB_PASSWD%@%DB_HOST%/%DB_NAME%');

/*
 * Login and registring method
 */
define('LOGIN_MODE', LOGIN_BY_EMAIL);
define('REGISTER_MODE', REGISTER_BY_EMAIL);

/*
 * Page encoding for the application
 */
define('APP_DEFAULT_ENCODING', 'utf-8');

/*
 * default helpers included for the whole application
 */
$helpers = array(
    // 'links', //Set of function to deal with web assets
    // 'forms', //usefull function form HTTP form
);
