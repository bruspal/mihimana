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


//Fichier qui stock et defini les constantes
/*
 * Constantes de chemins généraux
 */
define('MM_BASE_DIR', dirname(dirname(dirname(__FILE__))));
define('WEB_DIR', MM_BASE_DIR.'/web');
define('MM_LIB_DIR', MM_BASE_DIR.'/lib');
//define('FIXTURE_DIR', MM_LIB_DIR.'/fixtures');
define('MIHIMANA_DIR', MM_LIB_DIR.'/mihimana');
define('MM_CONFIG_DIR', MM_LIB_DIR.'/config');
//define('SQLITE_DIR', MM_LIB_DIR.'/sqlite');
define('MM_PLUGINS_DIR', MM_LIB_DIR.'/plugins');
define('MM_DOCTRINE_DIR', MM_PLUGINS_DIR.'/Doctrine-1.2.4');

/*
 * Constantes de chemin vers les dir spécifique a l'application
 */
define('APPLICATION_DIR', MM_BASE_DIR.DIRECTORY_SEPARATOR.APPLICATION);
define('LIB_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'lib');
define('MODELS_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'models');
define('CONFIG_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'config');
define('ASSETS_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'assets');
define('PLUGINS_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'plugins');
define('MIGRATION_DIR', APPLICATION_DIR.'/lib/migration');
define('TEMPLATES_DIR', APPLICATION_DIR.DIRECTORY_SEPARATOR.'templates');
define('VIEWS_DIR', TEMPLATES_DIR.DIRECTORY_SEPARATOR.'views');
define('PARTIALS_DIR', TEMPLATES_DIR.DIRECTORY_SEPARATOR.'partials');
define('FUNCTIONS_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'functions');
define('CLASSES_DIR', LIB_DIR.DIRECTORY_SEPARATOR.'classes');
/*
 * Constante URI
 */
if (array_key_exists('CONTEXT_PREFIX', $_SERVER)) {
    define('WEB_CONTEXT', $_SERVER['CONTEXT_PREFIX']);
} else {
    $posLastSlash = strrpos($_SERVER['SCRIPT_NAME'], '/');
    $webContext = substr($_SERVER['SCRIPT_NAME'], 0, $posLastSlash);
    define('WEB_CONTEXT', $webContext);
}

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
?>
