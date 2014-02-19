<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmForm
@file : mmMenu.php
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


class mmMenu extends mmWidget { // implements ArrayAccess {

  public $listeHtml, $editable, $nomMenu;
  
  
  public function __construct($nomMenu, $name='', $editable=false) {
    $this->attributes['name'] = $name;
    $this->attributes['value'] = $nomMenu;
    $this->attributes['id'] = $nomMenu;
    $this->charge($nomMenu, $editable);
    return $this;
  }
  
  public function charge($nomMenu, $editable=false) {
    $descriptionMenu = Doctrine_Core::getTable('DescriptionMenu')->createQuery()->
            where('nom = ?', $nomMenu)->
            orderBy('id')->
            execute();
    //on stock le nom interne du menu
    $this->nomMenu = $nomMenu;
    //on construit le tableau
    $tableauMenu = $this->genereTableau($descriptionMenu);
    $this->editable = $editable;
    if ($editable) {
      $this->listeHtml = $this->genereListHtmlEditable($tableauMenu);
    }
    else {
      $this->listeHtml = $this->genereListHtml($tableauMenu);
    }
  }

  public function __toString() {
    return $this->render();
  }
  
  /**
   * Retourne la chaine de caracteres contenant le menu
   * @return string
   */
  public function render() {
    $jScript = '
      <script type="text/javascript">
        sfHover = function() {
          var sfEls = document.getElementById("'.$this->nomMenu.'").getElementsByTagName("LI");
          for (var i=0; i<sfEls.length; i++) {
            sfEls[i].onmouseover=function() {
              this.className+=" sfhover";
            }
            sfEls[i].onmouseout=function() {
              this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
            }
          }
        }
        if (window.attachEvent) window.attachEvent("onload", sfHover);
      </script>';

//    return $jScript.'<ul id="'.$this->nomMenu.'" class="menu">'.$this->listeHtml.'</ul>';
    return '<ul id="nav" class="menu">'.$this->listeHtml.'</ul><script type="text/javascript" src="js/menu.js"></script>';
  }
  
  /*
   * Methode de travail 
   */
  /**
   * Genere la structure de donnée a partir de la description de la base de données
   * 
   * @param Doctrine_Collection $descriptionMenu
   * @return $array
   */
  public function genereTableau(Doctrine_Collection $descriptionMenu) {
    //On construit le tableau a partir des indice
    
    $tableauMenu = array();
    
    foreach ($descriptionMenu as $entree) {
      $pos1 = $entree['id'][0];
      $pos2 = $entree['id'][1];
      $pos3 = $entree['id'][2];
      $pos4 = $entree['id'][3];

      if ($pos4) {
        $tableauMenu[$pos1][$pos2][$pos3][$pos4] = array('label'=>$entree['label'],'script'=>$entree['script'], 'position'=>$entree['id']);
      }
      else {
        if ($pos3) {
          $tableauMenu[$pos1][$pos2][$pos3] = array('label'=>$entree['label'],'script'=>$entree['script'], 'position'=>$entree['id']);
        }
        else {
          if ($pos2) {
            $tableauMenu[$pos1][$pos2] = array('label'=>$entree['label'],'script'=>$entree['script'], 'position'=>$entree['id']);
          }
          else {
            $tableauMenu[$pos1] = array('label'=>$entree['label'],'script'=>$entree['script'], 'position'=>$entree['id']);
          }
        }
      }
    }
    return $tableauMenu;
  }
  
  /**
   * Genere la liste a puce a partir du tableau structuré
   * @param type $tableauMenu
   * @return string 
   */
  public function genereListHtml($tableauMenu) {
    $result = '';
    foreach ($tableauMenu as $key => $menu) {
      if (trim($menu['script'])) {
        $script = genereUrlProtege($menu['script']);
      }
      else {
        $script = '';
      }
      $result .= '<li>';
      $result .= sprintf('<a href="%s">%s</a>', $script, $menu['label']);
      //on traite les eventuel sous menu
      $chaineSousMenu = '';
      foreach ($menu as $sKey => $sMenu) {
        if ($sKey != 'label' && $sKey != 'script' && $sKey != 'position') {
          $chaineSousMenu .= $this->genereListHtml(array($sMenu));
        }
      }
      if ($chaineSousMenu) {
        $result .= '<ul>'.$chaineSousMenu.'</ul>';
      }
      $result .= '</li>';
    }
    return $result;
  }
  
  /**
   * Genere la liste a puce a partir du tableau structuré
   * @param type $tableauMenu
   * @return string 
   */
  public function genereListHtmlEditable($tableauMenu) {
    $result = '';
    foreach ($tableauMenu as $key => $menu) {
      $result .= '<li>';
      $result .= sprintf('<a href="#" onclick="alert(\'%s\');return false;">%s</a>', $menu['position'], $menu['label']);
      //on traite les eventuel sous menu
      $chaineSousMenu = '';
      foreach ($menu as $sKey => $sMenu) {
        if ($sKey != 'label' && $sKey != 'script' && $sKey != 'position') {
          $chaineSousMenu .= $this->genereListHtmlEditable(array($sMenu));
        }
      }
      if ($chaineSousMenu) {
        $result .= '<ul>'.$chaineSousMenu.'</ul>';
      }
      $result .= '</li>';
    }
    return $result;
  }
  
}
?>
