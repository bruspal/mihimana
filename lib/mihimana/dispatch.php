<?php namespace mm\dispatcher;

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: root
  @file : dispatch.php
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
  ------------------------------------------------------------------------------ */
/*
 * Dispatching
 */
try { //On protege contre les erreurs ce qui se trouve dans le try { }
    /*
     * Routing
     */
    $router = new \mmRouter();
    $request = $router->getRequest();

    $module = $request->get('module', MODULE_DEFAUT);
    $action = $request->get('action', ACTION_DEFAUT);
    //cleanup the request object : removing module and action
    $request->remove('module');
    $request->remove('action');
    //On declare les module et action courant dans des constante pour pouvoir les recuperer facilement dans les modules et scripts.
    define('MODULE_COURANT', $module);
    define('ACTION_COURANTE', $action);
    

    /**************************/
    // TODO : revoir le mécanisme de forcage de reponse JSON
    // /!\ _fhr_ a 1 pour forcer la reponse le mode AJAX
    // detection d'un appel AJAX. Detection automatique sauf si le parametre _fhr_ est present dans le request et vaut 1 
    // determine si la reponse doit etre du json ou non, par defaut oui si on est en mode ajax. Dans ce cas la on est en mode json sauf si on force explicitement
    // le mode http via le parametre _fhr_ fournis en parametre
    /**************************/
    if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        define('AJAX_REQUEST', true);
        $_fhr_ = $request->get('_fhr_', false);
        if ($_fhr_) {
            define('AJAX_RESPONSE', true);
        } else {
            define('AJAX_RESPONSE', false);
        }
    } else {
        define('AJAX_REQUEST', false);
        define('AJAX_RESPONSE', false);
    }

    //verification de l'authentification
    if (!\mmUser::isAuthenticated($module, $action)) {
        //Si on est en ajax on affiche le message et un bouton
        if (AJAX_REQUEST) {
            echo mmErrorMessageAjax('La session à expirée veuillez vous reconnecter<br /><button onclick="goPage(\'?\')">Reconnection</button>');
            die;
        }
        //on fait une redirection vers la page de login
        redirect(url('login'));
    }

    //detection si c'est un appel en clair ou du https
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        define('HTTPS', true);
    } else {
        define('HTTPS', false);
    }

    //ici deux choix : soit le fichier php existe. On le charge et l'execute. Sinon on charge et execute le programme générique d'extension
    $dispatcher_fichierProgramme = false;
    if (file_exists('../' . APPLICATION . '/' . $module . '.php')) {
        $dispatcher_fichierProgramme = '../' . APPLICATION . '/' . $module . '.php';
        define('PROGRAMME_STANDARD', false);
    } else {
        if (file_exists(MIHIMANA_DIR . '/builtinModule/' . $module . '.php')) {
            $dispatcher_fichierProgramme = MIHIMANA_DIR . '/builtinModule/' . $module . '.php';
            define('PROGRAMME_STANDARD', true);
        }
    }

    if ($dispatcher_fichierProgramme) {
        //fichier PHP personnalisé
        //inclusion du programme en fonction du module
        ob_start(); //on commence la bufferisation des sortie PHP
        require $dispatcher_fichierProgramme;
        if (class_exists($module)) { //est ce que le fichier charger contient une classe du meme nom que module ? dans ce cas la c'est un programme encapsulé dans une classe
            ob_clean(); //on vide le buffer de sortie pour avoir un buffer de sortie vide avant de commencer l'execution du programme
            //creation en memoire du programme
            $dispatcher_programmePhp = new $module();
            //on execute l'action du programme avec les parametres fournis au script par l'url
            $dispatcher_programmePhp->execute($request);
        } else {
            //on recupère le buffer PHP car le code contenu dans le module a deja été exécuté lors du require
            //on affiche ce buffer dans le template en faisant un require du layout
            $sortieProgramme = ob_get_clean();
            include APPLICATION_DIR . '/templates/layout.php';
        }
    } else {
        throw new \mmExceptionControl("<h1>$module : Module inexistant</h1>");
    }
} catch (\mmExceptionControl $e) {
    $sortieProgramme = '<h1>' . $e->getMessage() . '</h1>';
    include APPLICATION_DIR . '/templates/layout.php';
} catch (\mmExceptionRessource $e) {
    $sortieProgramme = '<h1>' . $e->getMessage() . '</h1>';
    include APPLICATION_DIR . '/templates/layout.php';
} catch (\Exception $e) {
    //Si une erreur non gerée se produit on affiche le message d'erreur detaillé si on est en mode DEBUG, sinon on fais autre chose (genre log, mail, etc)
    //TODO: voir comment gerer les erreurs critique en production
    $sortieProgramme = ob_get_clean();
    if (DEBUG) {
        //En mode debug ? On affiche les informations détaillées sur l'erreur
        $sortieProgramme .="<fieldset><legend>Erreur</legend>" . $e->getMessage() . "</fieldset>
    <fieldset><legend>Trace</legend><pre>" . $e->getTraceAsString() . "</pre>
    </fieldset>";
    } else {
        //En mode production on affiche une erreur generaliste
        $sortieProgramme = "<h1>Une erreur critique a eu lieu. Veuillez contacter le service informatique.</h1>";
    }
    if ( ! AJAX_REQUEST) {
        include APPLICATION_DIR . '/templates/layout.php';
    }
}
?>
