<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmForm/widgets
@file : mmWidgetButtonGoPage.php
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


class mmWidgetButtonGoPage extends mmWidgetButton {
  /**
   * Creer un bouton qui permait de changer de page dans le cas d'une execution classique ou d'afficher la page dans la popup ajax si on est en mode ajax.
   * @param string $libelle : Libelle du bouton
   * @param string $url : url de destination
   * @param boolean $replace : si il est positionné a vrai effectue un appel standard avec rechargement de la page complet. Si on etait en context ajax, on quitte se contexte
   * @param string $name 
   */
  public function __construct($libelle, $url, $replace = false, $name = '', $attributs = array()) {
    if ( ! $name) {
      $name = str_replace(' ', '_', lcfirst($libelle));
    }
    if ( ! DEBUG)
    {
      $url = genereUrlProtege($url);
    }
    
//    //on definie le type d'appel pour effectuer le bon appel, soit on rafraichis le navigateur
//    //soit on rafraichis la dialoguebox
//    if (AJAX_REQUEST && ! $replace)
//    {
//      //C'est un appel ajax, on met a jour la sous fenetre
//      $attributs['onclick']="$('#__mdDialog').jqmHide();mdAjaxHtmlDialog('$url')";
//    }
//    else
//    {
//      $attributs['onclick'] = "goPage('$url')";
//    }
    parent::__construct($name, $libelle, $attributs);
    $this->setUrl($url, $replace);
  }
  
  public function setUrl($url, $replace = false)
  {
    //on definie le type d'appel pour effectuer le bon appel, soit on rafraichis le navigateur
    //soit on rafraichis la dialoguebox
    if (AJAX_REQUEST && ! $replace)
    {
      //C'est un appel ajax, on met a jour la sous fenetre
      $this->attributes['onclick']="$('#__mdDialog').jqmHide();mdAjaxHtmlDialog('$url')";
    }
    else
    {
      $this->attributes['onclick'] = "goPage('$url')";
    }
  }
  
}
?>
