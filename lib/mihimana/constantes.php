<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: root
@file : constantes.php
-------------------------------------

This file is part of Mihimana.

Mihimana is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Mihimana is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
------------------------------------------------------------------------------*/

if ( ! defined('ENVIRONNEMENT')) {
    define('ENVIRONNEMENT', 'dev');
}
if ( ! defined('DEBUG')) {
    if (ENVIRONNEMENT == 'dev') {
        define('DEBUG', true);
    } else {
        define('DEBUG', false);
    }
}

//Fichier qui stock et defini les constantes
/*
 * Constants for general mihimana frameworks directories
 */
define('MM_BASE_DIR', dirname(dirname(dirname(__FILE__))));
define('MM_LIB_DIR', MM_BASE_DIR.'/lib');
//define('FIXTURE_DIR', MM_LIB_DIR.'/fixtures');
define('MIHIMANA_DIR', MM_LIB_DIR.'/mihimana');
define('MM_CONFIG_DIR', MM_LIB_DIR.'/config');
//define('SQLITE_DIR', MM_LIB_DIR.'/sqlite');
define('MM_PLUGINS_DIR', MM_LIB_DIR.'/plugins');
define('MM_DOCTRINE_DIR', MM_PLUGINS_DIR.'/Doctrine-1.2.4');
define('MM_HELPERS_DIR', MIHIMANA_DIR.DIRECTORY_SEPARATOR.'helpers');

/*
 * Constants for application's specific directories
 */
if ( !defined('APPLICATION_DIR')) {
    define('APPLICATION_DIR', MM_BASE_DIR.DIRECTORY_SEPARATOR.APPLICATION);
}
if ( ! defined('WEB_DIR')) {
    define('WEB_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'web');
}
if ( !  defined('SCRIPT_DIR')) {
    define('SCRIPT_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'script_console');
}
if ( ! defined('ASSETS_DIR')) {
    define('ASSETS_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'assets');
}

//if ( ! defined('APPLICATION_SCRIPT')) { //what is the name of the applications's entry point file
//    define ('APPLICATION_SCRIPT', basename(__FILE__));
//}

define('LIB_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'lib');
define('MODELS_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'models');
if ( ! defined('CONFIG_DIR')) {
    define('CONFIG_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'config');
}
if ( ! defined('CONFIG_FILE')) {
    define('CONFIG_FILE', 'config.php');
}
define('PLUGINS_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'plugins');
//define('MIGRATION_DIR', APPLICATION_DIR.'/lib/migration');
define('MIGRATION_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'migration');
define('TEMPLATES_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'templates');
define('VIEWS_DIR', TEMPLATES_DIR.DIRECTORY_SEPARATOR.'views');
define('PARTIALS_DIR', TEMPLATES_DIR.DIRECTORY_SEPARATOR.'partials');
define('FUNCTIONS_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'functions');
define('CLASSES_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'classes');
define('HELPERS_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'helpers');

/*
 * Constant URI
 */
//if (array_key_exists('CONTEXT_PREFIX', $_SERVER)) {
//    define('WEB_CONTEXT', $_SERVER['CONTEXT_PREFIX']);
//} else {
    $posLastSlash = strrpos($_SERVER['SCRIPT_NAME'], '/');
    $webContext = substr($_SERVER['SCRIPT_NAME'], 0, $posLastSlash);
    define('WEB_CONTEXT', $webContext);
//}

/*
 * Constantes pdf
 */
define('MM_PDF_AUTHOR', 'MihiMana Engine');
define('MM_PDF_CREATOR', 'MihiMana Engine');
/*
 * Precision mathématique et devise
 */
define('MM_DEVISE_DECIMAL', 0);
define('MM_DEVISE_SYMBOL', '');
/*
 * Login system
 */
define('LOGIN_BY_USER', 0);     //On utilise le login de l'utilisateur pour les opération de login
define('LOGIN_BY_EMAIL', 1);    //On utilise l'email pour se connecter'
define('LOGIN_BY_BOTH', 2);     //On utilise l'email ou le login
/*
 * Register system
 */
define('REGISTER_BY_USER', 0);     //On utilise le login de l'utilisateur pour les opération de login
define('REGISTER_BY_EMAIL', 1);    //On utilise l'email pour se connecter'

/*
 * Mail constants
 */
//server method
define('MM_MAIL_SERVER_SMTP', 0);
define('MM_MAIL_SERVER_SENDMAIL', 1);
//mail format
define('MM_MAIL_PLAINTEXT', 0);
define('MM_MAIL_HTML', 1);
//SMTP secure
define('MM_MAIL_SMTP_SECURE_NONE', 0);
define('MM_MAIL_SMTP_SECURE_TLS', 1);
define('MM_MAIL_SMTP_SECURE_SSL', 2);