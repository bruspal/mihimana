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
@file : mihimana.php
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

// Define default constants value if not defined in config.php or application index file
if (!defined('APPLICATION')) {
	Die("L'application n'a pas été définie");
}
session_name('sess_' . APPLICATION);
session_start();
//Lecture des constante
require 'constantes.php';
//Config reading
require CONFIG_DIR . DIRECTORY_SEPARATOR .CONFIG_FILE;
// web specifique constantes
if (!defined('MODULE_DEFAUT')) {
    define('MODULE_DEFAUT', 'main');
}
if (!defined('ACTION_DEFAUT')) {
    define('ACTION_DEFAUT', 'index');
}
if (!defined('DEBUG')) {
    define('DEBUG', false);
}
if (!defined('MODE_INSTALL')) {
    define('MODE_INSTALL', false);
}
if (!defined('NO_LOGIN')) {
    define('NO_LOGIN', false);
}
if (!defined('SUPER_ADMIN')) {
    define('SUPER_ADMIN', false);
}
if (! defined('APP_DEFAULT_ENCODING')) {
    define('APP_DEFAULT_ENCODING', 'utf-8');
}
if (!defined('LOGIN_MODE')) {
    define('LOGIN_MODE', LOGIN_BY_USER);
}
if (!defined('REGISTER_MODE')) {
    define ('REGISTER_MODE', REGISTER_BY_USER);
}
if (!defined('HANDHELD_AUTODETECT')) {
    define ('HANDHELD_AUTODETECT', true);
}
//Error reporting
if (DEBUG) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
    error_reporting(0);
}
//on charge toutes les fonctions se trouvant dans le repertoire mihimana/functions
$liste = new DirectoryIterator(MIHIMANA_DIR . '/functions');
foreach ($liste as $fichier) {
    if ($fichier->isFile()) {
        require_once $fichier->getPathname();
    }
}
//On ajoute les fonction de l'utilisateur
if (is_dir(FUNCTIONS_DIR)) {
    $liste = new DirectoryIterator(FUNCTIONS_DIR);
    foreach ($liste as $fichier) {
        if ($fichier->isFile()) {
            require_once $fichier->getPathname();
        }
    }
}
//chargemet de Doctrine et autoload des classe doctrine
//et bootstrapping (config initiale)
require_once MM_DOCTRINE_DIR . '/Doctrine.php';
//configuration de Doctrine
Doctrine_Core::debug(DEBUG);
//autoload register
spl_autoload_register(array('Doctrine_Core', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));

$mdManager = Doctrine_Manager::getInstance();

$mdManager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE); //VALIDATE_CONSTRAINTS | Doctrine_Core::VALIDATE_LENGTHS);
$mdManager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
$mdManager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
$mdManager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

try {
    $mmConnData = Doctrine_Manager::connection(DATABASE, 'data');
    $mmConnData->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
    $mmConnData->setCharset('utf8');
//    $mmConnData->setAttribute(Doctrine_Core::ATTR_CASCADE_SAVES, false);
} catch (Doctrine_Manager_Exception $e) {
    die($e->getMessage());
}

Doctrine_Core::loadModels(MODELS_DIR);

//classes cache init
if ( ! array_key_exists('__classesCache__', $_SESSION)) {
    $_SESSION['__classesCache__'] = array();
}
//AutoLoad de mihimana
spl_autoload_register('mmAutoload');

/* * ************************************
 * FIN DU PARAMETRAGE
 * ************************************ */

//Dispatcheur
require_once 'dispatch.php';

//Fonction de callback pour l'autoload des classes de mihimana
function mmAutoload($className) {
    //looking for class in cache
    if (array_key_exists($className, $_SESSION['__classesCache__'])) {
        require_once $_SESSION['__classesCache__'][$className];
        return true;
    }
    //On cherche dans mihimana
    $found = __autoloadScanRepertoire($className, MIHIMANA_DIR);
    if (!$found) {
        //On cherche dans le repertoire de l'application courante.
        $found = __autoloadScanRepertoire($className, CLASSES_DIR);
    }
    //trouve ou pas en renvoie le resultat
    return $found;
}

//function de parcourt et de chargement des fichiers automatique
function __autoloadScanRepertoire($className, $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        $currentFile = $file->getBasename();
        if ($className . '.php' == $currentFile) {
            //File found ?
            $filePath = $file->getPathname();
            //add to session array them require and quit
            $_SESSION['__classesCache__'][$className] = $filePath;
            require_once $filePath;
            return true;
        }
    }
    //Si on arrive ici c'est qu'on a pas trouvé le fichier, on renvoie faux
    return false;
}

?>