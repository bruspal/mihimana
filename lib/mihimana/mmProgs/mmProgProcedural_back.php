<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmProgs
@file : mmProgProcedural_back.php
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


class mmProgProcedural_back extends mmObject {
  //
  protected 
          $layout,
          $template;
  
  public
          $variables;
    
  public function __construct() {
    $this->layout = 'layout.php';
    $this->template = "html_{$GLOBALS['module']}.php";
    $this->variables = array();
  }
  
  /**
   * Execute l'action $action et affiche le resultat
   * @param string $action
   * @param array $request
   * @return boolean 
   */
  
  public function execute($action, mmRequest $request) {
    //On sauvegarde les parametres passé au programme
    //demarage du buffer
    ob_clean();
    ob_start();
    //execution
    $codeSortie = $this->principale($action, $request);
    //recuperation du buffer de sortie
    $sortieProgramme = ob_get_clean();
    //Application du layout de l'ecran si un layout existe
    if ($this->template && file_exists(getTemplatesPath().'/'.$this->template))
    {
      //y'a un template, on le parse
      $sortieLayout = mmTemplate::renderTemplate($this->template, $this->variables, getTemplatesPath());
    }
    else
    {
      //pas de templates associe, on a une chaine vide
      $sortieLayout = '';
    }
    //on chaine le layout au sortie brute du programme
    $sortieProgramme = $sortieProgramme.$sortieLayout;
    
    if ( ! AJAX_REQUEST && $this->layout)
    {
      $sortieFinale = mmTemplate::renderTemplate($this->layout, array('sortieProgramme'=>$sortieProgramme), APPLICATION_DIR.'/templates');
    }
    else
    {
      $sortieFinale = $sortieProgramme;
    }
    //affichage de l'ecran
    echo $sortieFinale;
    
    return true;
  }
  
  public function principale($action = '', $parametres = null) {
    
  }
  
  //Method de gestion interne
  /**
   * Definie le layout a utiliser, par defaut 'layout'. SI mis a null ou false, la sortie se ferra sans layout. Retourne le nopm de l'ancien layout
   * @param string $nomLayout
   * @return string ancien nom du layout
   */
  protected function setLayout($nomLayout) {
    $oldLayout = substr($this->layout, 0, strpos($this->layout, '.php'));
    if ($nomLayout)
    {
      $this->layout = $nomLayout.'.php';
    }
    else
    {
      $this->layout = '';
    }
    return $oldLayout;
  }
  
  protected function getLayout() {
    return substr($this->layout, 0, strpos($this->layout, '.php'));
  }
  
  protected function setTemplate($nomTemplate = false) {
    $oldTemplate = substr($this->template, 0, strpos($this->template, '.php'));
    if ($nomTemplate)
    {
      $this->template = $nomTemplate.'.php';
    }
    else
    {
      $this->template = false;
    }
    
    return $oldTemplate;
  }
  
  protected function getTemplate() {
    if ($this->template)
    {
      return substr($this->template, 0, strpos($this->template, '.php'));
    }
    else
    {
      return false;
    }
  }
  
  /**
   * Effectu un redirect
   * @param type $url
   * @param type $protegeUrl 
   */
  public function redirect($url, $protegeUrl = true) {
    redirect($url, $protegeUrl);
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
    }
    else {
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
?>
