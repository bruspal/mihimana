<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: 
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



session_name('sess_' . APPLICATION);
session_start();
//Lecture des constante
require_once 'constantes.php';
require_once APPLICATION_DIR . '/config/config.php';
//Verification d'usage pour garantire que l'application fonctionnera correctement
//test des constantes
if (!defined('APPLICATION')) {
    Die("L'application n'a pas été définie");
}
if (!defined('MODULE_DEFAUT')) {
    Die("Pas de module par defaut défini");
}
if (!defined('ACTION_DEFAUT')) {
    define('ACTION_DEFAUT', 'index');
    ;
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
//Mise en place du niveau de report d'erreur
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

//chargemet de Doctrine et autoload des classe doctrine
//et bootstrapping (config initiale)
require_once DOCTRINE_DIR . '/Doctrine.php';
spl_autoload_register(array('Doctrine_Core', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));

$mdManager = Doctrine_Manager::getInstance();

$mdManager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE); //VALIDATE_CONSTRAINTS | Doctrine_Core::VALIDATE_LENGTHS);  
$mdManager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
$mdManager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
$mdManager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

try {
//  $mdConnParam = Doctrine_Manager::connection(DATABASE_PARAM, 'param');
    $mdConnData = Doctrine_Manager::connection(DATABASE, 'data');
} catch (Doctrine_Manager_Exception $e) {
    die($e->getMessage());
}


Doctrine_Core::loadModels(MODELS_DIR);

//AutoLoad de mihimana
spl_autoload_register('mdAutoload');

/* * ************************************
 * FIN DU PARAMETRAGE
 * ************************************ */

//Dispatcheur
require_once 'dispatch.php';

//Fonction de callback pour l'autoload des classes de mihimana
function mdAutoload($className) {
    //On cherche dans mihimana
    $trouve = __autoloadScanRepertoire($className, MIHIMANA_DIR);
    if (!$trouve) {
        //On cherche dans le repertoire de l'application courante.
        $trouve = __autoloadScanRepertoire($className, APPLICATION_DIR);
    }
    //trouve ou pas en renvoie le resultat
    return $trouve;
}

//function de parcourt et de chargement des fichiers automatique
function __autoloadScanRepertoire($className, $repertoire) {
    $parcourt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repertoire));
    foreach ($parcourt as $fichier) {
        $fichierCourant = $fichier->getBasename();
        if ($className . '.php' == $fichierCourant) {
            //on a trouver le fichier ?
            $chemin = $fichier->getPathname();
            require_once $chemin; //on inclus le fichier et on quite en renvoyant vrai
            return true;
        }
    }
    //Si on arrive ici c'est qu'on a pas trouvé le fichier, on renvoie faux
    return false;
}

?>