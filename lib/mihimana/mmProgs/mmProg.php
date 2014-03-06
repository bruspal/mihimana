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
            $parametresProgramme;
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
    public function execute(mmRequest $request) {
        $action = ACTION_COURANTE;
        //On sauvegarde les parametres passé au programme
        $this->parametresProgramme = $request;
        //On definie quelle action a executer
        $methodAction = 'execute' . ucfirst($action); //action a executer
        
    //demarage du buffer
        ob_clean();
        ob_start();
        //execution
        $this->configure($request);
        $this->preExecute($request);
        if (method_exists($this, $methodAction)) {
            $codeSortie = $this->$methodAction($request);
        } else {
            throw new mmExceptionRessource("unknown action : $action");
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
            $templateToParse = file_exists(getTemplatesPath() . '/' . $this->templateModuleAction) ? $this->templateModuleAction : (file_exists(getTemplatesPath() . '/' . $this->templateModule) ? $this->templateModule : false);
            if ($templateToParse) {
                //y'a un template, on le parse
                $templateContent = mmTemplate::renderTemplate($templateToParse, $this->variables, getTemplatesPath());
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

        $this->genereHtmlFinal($sortieFinale);

        return true;
    }

    public function genereHtmlFinal($html) {
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

    protected function setTemplate($nomTemplate = false) {
        $oldTemplate = substr($this->templateModuleAction, 0, strpos($this->templateModuleAction, '.php'));
        if ($nomTemplate) {
            $this->templateModuleAction = $nomTemplate . '.php';
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
     * effectue un redirect
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