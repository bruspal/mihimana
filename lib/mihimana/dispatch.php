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

//Force outpu as Html
mmOutputHtml();

//TODO: make a better try catch
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
        define('AJAX_RESPONSE', true);
//        $_fhr_ = $request->get('_fhr_', true);
//        if ($_fhr_) {
//            define('AJAX_RESPONSE', true);
//        } else {
//            define('AJAX_RESPONSE', false);
//        }
    } else {
        define('AJAX_REQUEST', false);
        define('AJAX_RESPONSE', false);
    }

    // check for maintenance mode
    if (file_exists(APPLICATION_DIR.DIRECTORY_SEPARATOR.'maintenance')) {
        if (AJAX_RESPONSE) {
	        $content = file_get_contents(APPLICATION_DIR.DIRECTORY_SEPARATOR.'maintenance');
	        if(empty($content)) {
		        $content = 'The application is presently in mantenance. It won\'t be long.';
	        }
            \mmJSON::sendForbidden($content);
            die;
        }
	    header('HTTP/1.0 403 Forbidden');
        if (file_exists(TEMPLATES_DIR.DIRECTORY_SEPARATOR.'maintenance.php')) {
            require_once TEMPLATES_DIR.DIRECTORY_SEPARATOR.'maintenance.php';
        } else {
            ?>
    <html>
        <head>
            <title>Maintenance</title>
        </head>
        <body><h1>Down for maintenance</h1>It won't be very long. Come to see us soon !</body>
    </html>
            <?php
            die;
        }
    }

    //verification de l'authentification
    if (!\mmUser::isAuthenticated($module, $action)) { // not identified
        if( ! \mmUser::isAuthorized($module, $action)) { // nor authorized
            //if ajax call juste echo error message
            //TODO: allow JSON response for webservice authentification
            if (AJAX_REQUEST) {
                if (AJAX_RESPONSE) {
                    \mmJSON::sendUnauthorized();
                    die;
                } else {
                    echo mmErrorMessageAjax('La session à expirée veuillez vous reconnecter<br /><button onclick="goPage(\'?\')">Reconnection</button>', 1);
                    die;
                }
            } else {
                //on fait une redirection vers la page de login
                redirect(url('login'));
            }
        }
    }

    //detection si c'est un appel en clair ou du https
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        define('HTTPS', true);
    } else {
        define('HTTPS', false);
    }

    //ici deux choix : soit le fichier php existe. On le charge et l'execute. Sinon on charge et execute le programme générique d'extension
    $dispatcher_fichierProgramme = false;
    if (file_exists(APPLICATION_DIR.DIRECTORY_SEPARATOR.$module . '.php')) {
        $dispatcher_fichierProgramme = APPLICATION_DIR.DIRECTORY_SEPARATOR.$module . '.php';
        define('PROGRAMME_STANDARD', false);
    } else {
        if (file_exists(MIHIMANA_DIR . '/builtinModule/' . $module . '.php')) {
            $dispatcher_fichierProgramme = MIHIMANA_DIR . '/builtinModule/' . $module . '.php';
            define('PROGRAMME_STANDARD', true);
        }
    }

    //Defined defaults helpers
    if (isset($helpers)) { //on a rajouté des helpers dans le fichier de config
        $helpers = (array) $helpers;
        loadHelper($helpers);
    }

    //Check for defauls plugins
    if (isset($plugins)) {
        loadPlugin($plugins);
    }

    //client type determination
    $md = new \Mobile_Detect();
    $isMobile = $md->isMobile();
    $isTablet = $md->isTablet();
    if (DEBUG) {
        /*
         * in debug mode it is possible to force mode with url parameter _ForceClient_
         * value are :
         *      mobile: to force mobile
         *      tablet: to force tablet
         *      desktop: to force desktop
         *      default (or anything else): to reset setting
         * The setting still logout or 'default' call
         */
        $forcedClient = $request->get('_ForceClient_',false);
        if ($forcedClient) {
            \mmUser::set('_ForceClient_', $forcedClient);
        }
        $forcedClient = \mmUser::get('_ForceClient_', false);
        if ($forcedClient) {
            switch ($forcedClient) {
                case 'mobile':
                    $isMobile = true;
                    $isTablet = false;
                    break;
                case 'tablet':
                    $isMobile = false;
                    $isTablet = true;
                    break;
                case 'desktop':
                    $isMobile = false;
                    $isTablet = false;
                    break;
                default:
                    \mmUser::remove('_ForceClient_');
                    break;
            }
        }
    }

    if ($isMobile || $isTablet) {
        define('CLIENT_DESKTOP', false);
        define('CLIENT_HANDHELD', true);
        define('CLIENT_MOBILE', $isMobile);
        define('CLIENT_TABLET', $isTablet);
    } else {
        define('CLIENT_DESKTOP', true);
        define('CLIENT_HANDHELD', false);
        define('CLIENT_MOBILE', false);
        define('CLIENT_TABLET', false);
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
	        if (method_exists($dispatcher_programmePhp, 'execute')) {
                $dispatcher_programmePhp->execute($action, $request);
	        }
        } else {
            //on recupère le buffer PHP car le code contenu dans le module a deja été exécuté lors du require
            //on affiche ce buffer dans le template en faisant un require du layout
            $sortieProgramme = ob_get_clean();
            include APPLICATION_DIR . '/templates/layout.php';
        }
    } else {
        throw new \mmExceptionHttp(\mmExceptionHttp::NOT_FOUND);
    }
} catch (\mmExceptionControl $e) { //exception de controle
    $sortieProgramme = '<h1>' . $e->getMessage() . '</h1>';
    echoError($sortieProgramme);
} catch (\mmExceptionHttp $e) { //exception HHTP
    $sortieProgramme = '<h1>' . $e->getMessage() . '</h1>';
    echoError($sortieProgramme);
} catch (\mmExceptionRessource $e) { //Exception de ressources
    $sortieProgramme = '<h1>' . $e->getMessage() . '</h1>';
    echoError($sortieProgramme);
} catch (\mmExceptionUser $e) {
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();

    switch ($GLOBALS['OUTPUT_MODE']) {
        case 'json':
            \mmJSON::sendJSONError($errorCode, $errorMessage);
            break;
        case 'jsonp':
            \mmJSON::sendJSONError($errorCode, $errorMessage);
            break;
        case 'html':
        default:
            $sortieProgramme = "<div><h1>Erreur</h1><div>$errorCode : $errorMessage</div></div>";
            include APPLICATION_DIR . '/templates/layout.php';
            break;
    }
} catch (\Exception $e) { //tous les autres cas
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
    echoError($sortieProgramme);
//    if ( ! AJAX_REQUEST) {
//        include APPLICATION_DIR . '/templates/layout.php';
//    }
}

function echoError($contenu, $errorCode = -500, $errorMessage = 'Erreur interne') {
    if (DEBUG) {
        $sortieProgramme = $contenu;
    } else {
        $sortieProgramme = '';
    }
    //set status as internal error
    //header('HTTP/1.0 500 Internal Error');

    switch ($GLOBALS['OUTPUT_MODE']) {
        case 'json':
            \mmJSON::sendJSON(null, false, $errorCode, $errorMessage."\n".$sortieProgramme);
            break;
        case 'jsonp':
            \mmJSON::sendJSONP(null, false, $errorCode, $errorMessage."\n".$sortieProgramme);
            break;
        case 'html':
        default:
            include APPLICATION_DIR . '/templates/layout.php';
            break;
    }
}
?>
