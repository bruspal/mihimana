<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmProgs
  @file : mmProg.php
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

/**
 *  mmProg classe générique de programme piloté par action
 */
class mmProg extends mmObject {

    //
    protected
            $layout,
            $templateModuleAction, //template associé a un module + action
            $templateModule, //template associé a un module
            $parametresProgramme,
            $autoDetectMobile =false,
            $isMobile =false,
            $isTablet = false,
            $headers = array();
    public
            $variables;

    public function __construct() {
        $this->layout = 'layout.php';
//    $this->template = "html_{$GLOBALS['module']}.php";
        $this->templateModuleAction = "view_" . MODULE_COURANT . "#" . ACTION_COURANTE . ".php";
        $this->templateModule = "view_" . MODULE_COURANT . ".php";
        $this->variables = array();
    }

    /**
     * Execute l'action $action et affiche le resultat
     * @param string $action
     * @param array $request
     * @return boolean
     */
    public function execute($action, mmRequest $request) {
//        $action = ACTION_COURANTE;

        if (HANDHELD_AUTODETECT) {
            $this->detectMobile(true);
        }

        //get the request methode prefix, by default the request is execute, that means a get request
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'DELETE': //delete method
                $requestMethod = 'delete';
                break;
            //for now other methods are treated as get
            case 'PUT':
            case 'POST':
            default:
                $requestMethod = 'execute';
                break;
        }

        //On sauvegarde les parametres passé au programme
        $this->parametresProgramme = $request;
        //On definie quelle action a executer

//        $methodAction = 'execute' . ucfirst($action); //action a executer
        $methodAction = $requestMethod . ucfirst($action); //action a executer

    //demarage du buffer
        ob_clean();
        ob_start();
        //execution
        $this->configure($request);
        $this->preExecute($request);
        if (method_exists($this, $methodAction)) {
            $codeSortie = $this->$methodAction($request);
        } else {
            throw new mmExceptionHttp(mmExceptionHttp::NOT_FOUND);
        }
        $this->postExecute($request);
        //recuperation du buffer de sortie
        $sortieProgramme = ob_get_clean();
        //par defaut on considere qu'il n'y a pas de template
        $templateContent = '';
        //on ecrase le template vide si jamais on en trouve 1
        //le nommage des templates est le suivant :
        // view_NOMDUMODULE.NOMDELACTION.php ou view_NOMDUMODULE.php
        // la premiere ecriture est prioritaire sur la deuxieme. ainsi on peut definir que view_MODULE#ACTION.php sera executé lors de l'appel de ce module et de cet action, pour tous les autre cas ce sera view_MODULE.php
        if ($this->templateModuleAction) {
            $templateToParse = file_exists(getViewsPath() . DIRECTORY_SEPARATOR . $this->templateModuleAction) ?
                                       $this->templateModuleAction :
                               (file_exists(getViewsPath() . DIRECTORY_SEPARATOR . $this->templateModule) ?
                                       $this->templateModule : false);

            if ($templateToParse) {
                //y'a un template, on le parse
                $templateContent = mmTemplate::renderTemplate($templateToParse, $this->variables, getViewsPath());
            }
        }
        //on chaine le layout au sortie brute du programme
        $sortieProgramme = $sortieProgramme . $templateContent;

        if (!AJAX_REQUEST && $this->layout) {
            $sortieFinale = mmTemplate::renderTemplate($this->layout, array('sortieProgramme' => $sortieProgramme), APPLICATION_DIR . '/templates');
        } else {
            $sortieFinale = $sortieProgramme;
        }
        //affichage de l'ecran

        $this->renderHtml($sortieFinale);

        return true;
    }

    public function renderHtml($html) {
        //headers
        foreach ($this->headers as $header) {
            header($header[0], $header[1]);
        }
        if (!DEBUG) { // si on est pas en mode debug on vire tous les espace blanc saut de ligne et etc
            echo preg_replace('/\s/', ' ', $html);
        } else {
            echo $html;
        }
    }

    public function configure(mmRequest $request) {

    }

    /**
     * Code toujours executer avant une action
     */
    public function preExecute(mmRequest $request) {

    }

    /**
     * Code toujours executer apres une action
     */
    public function postExecute(mmRequest $request) {

    }

    public function executeIndex(mmRequest $request) {
        echo "Pas d'action par defaut definie";
    }

    //Method de gestion interne

    /**
     * Definie le layout a utiliser, par defaut 'layout'. SI mis a null ou false, la sortie se ferra sans layout. Retourne le nopm de l'ancien layout
     * @param string $nomLayout
     * @return string ancien nom du layout
     */
    protected function setLayout($nomLayout = false) {
        $oldLayout = substr($this->layout, 0, strpos($this->layout, '.php'));
        if ($nomLayout) {
            $this->layout = $nomLayout . '.php';
        } else {
            $this->layout = '';
        }
        return $oldLayout;
    }

    protected function getLayout() {
        return substr($this->layout, 0, strpos($this->layout, '.php'));
    }

    /**
     * This method enable mobile/tablet auto selection. If set to true (default) render will use phone_MODULENAME#ACTION instead of view_MODULENAME#ACTION if the client
     * is a phone or a tablet.
     * <!--
     * TODO : set template [phone|tablet]_MODULENAME#ACTION
     * if one of the template is missing the closest template will be used:<br>
     * <table>
     * <tr><td colspan="2">Not yet fully implemented<br>for now there is no auto selection, all go to phone_*</td></tr>
     * <tr><td>MISSING</td><td>USED</td></tr>
     * <tr><td>phone</td><td>tablet</td></tr>
     * <tr><td>tablet</td><td>phone</td></tr>
     * <tr><td>view</td><td>tablet</td></tr>
     * </table>
     * -->
     * @param type $mode enabled(true) or disabled(false)
     */
    protected function detectMobile($mode = true) {
        $this->autoDetectMobile = $mode;
        if ($mode === true) {
            $this->isMobile = CLIENT_MOBILE;
            $this->isTablet = CLIENT_TABLET;
            if ($this->isMobile || $this->isTablet) {
                $prefix = 'phone_';
                $templateModuleAction = getViewsPath() . DIRECTORY_SEPARATOR . $prefix . MODULE_COURANT . "#" . ACTION_COURANTE . ".php";
                $templateModule = getViewsPath() . DIRECTORY_SEPARATOR . $prefix . MODULE_COURANT . ".php";

                //one of the templates exists ? phone_ prefix can be used
                if (file_exists($templateModuleAction) || file_exists($templateModule)) {
                    $prefix = 'phone_';
                } else {
                    $prefix = 'view_';
                }

            } else {
                $prefix = 'view_';
            }
        } else {
            $this->isMobile = false;
            $this->isTablet = false;
            $prefix = 'view_';
        }
        $this->templateModuleAction = $prefix . MODULE_COURANT . "#" . ACTION_COURANTE . ".php";
        $this->templateModule = $prefix . MODULE_COURANT . ".php";
    }

    /**
     * Set the template for module/action. Templates are located in the PROJECT_ROOT/templates/views. By default name format is 'view_'.module[#action].php<br>
     * if #action is ommited this template become the template for all actions. Otherwise the template is used for the particular module/action<br>
     *
     * @param string $templateName If ommited or false disable the use of templating.<br>if the string is formated '#action_name' 'action_name' template will be used.
     * @return string the full name of the previous template name (without extension)
     */
    protected function setTemplate($templateName = false) {
        if ($this->autoDetectMobile) {
            if ($this->isMobile || $this->isTablet) {
                $prefix = 'phone_';
            }
        } else {
            $prefix = 'view_';
        }
        $oldTemplate = substr($this->templateModuleAction, 0, strpos($this->templateModuleAction, '.php'));
        if ($templateName) {
            if ($templateName[0] == '#') {
                // templateName start with # the template is set to the current module + action mode

                if ($this->autoDetectMobile && ($this->isMobile || $this->isTablet)) {
                    //special case if mobile detection is enabled. fallback to view_* if $prefix_* not found.
                    //it will drive to regular behavior if phone and view is missing
                    $tempName = $prefix . MODULE_COURANT . $templateName;
                    if (file_exists($tempName.'.php')) {
                        $templateName = $tempName;
                    } else {
                        $templateName = 'view_' . MODULE_COURANT . $templateName;
                    }
                } else {
                    $templateName = 'view_' . MODULE_COURANT . $templateName;
                }
            }
            $this->templateModuleAction = $templateName . '.php';
        } else {
            $this->templateModuleAction = false;
        }

        return $oldTemplate;
    }

    protected function getTemplate() {
        if ($this->templateModuleAction) {
            return substr($this->templateModuleAction, 0, strpos($this->templateModuleAction, '.php'));
        } else {
            return false;
        }
    }
    /**
     * add an header to the module
     * @param type see php::header()
     * @param type see php::header()
     */
    protected function addHeader($strHeader, $replace = true) {
        $this->headers[] = array($strHeader, $replace);
    }

    protected function outputAsJson() {
        $this->setTemplate(false);
        $this->setLayout(false);
        mmOutputJson();
    }

    protected function outputAsJsonp() {
        $this->setTemplate(false);
        $this->setLayout(false);
        mmOutputJsonp();
    }

    protected function outputAsHtml($encoding = APP_DEFAULT_ENCODING) {
        mmOutputHtml($encoding);
    }

    /**
     * do a http redirection
     * @param type $url
     * @param type $protegeUrl
     */
    public function redirect($url, $protegeUrl = true) {
        redirect($url, $protegeUrl);
///*
// * "HTTP/1.1 302 Found"
// * "text/html; charset=utf-8"
// */
//    if ($protegeUrl)
//    {
//      $url = genereUrlProtege($url);
//    }
//    ob_clean(); //on vide le buffer de sortie au cas ou pour eviter les erreurs
//    header("HTTP/1.1 302 Found");
//    header("Location: $url");
//    die();
    }

    /*
     * Setter et getter automatique. Permet de stocker les valeurs dans le tableau des variables, utile pour l'interpretation des templates
     *
     */

    public function __set($name, $value) {
        $this->variables[$name] = $value;
    }

    public function __get($name) {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        } else {
            throw new mmExceptionDev("La variable $name n'existe pas");
        }
    }

    public function __isset($name) {
        return isset($this->variables[$name]);
    }

    public function __unset($name) {
        unset($this->variables[$name]);
    }

}