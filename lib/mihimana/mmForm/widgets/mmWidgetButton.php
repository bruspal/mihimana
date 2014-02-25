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
@file : mmWidgetButton.php
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


class mmWidgetButton extends mmWidget {
  
  public function __construct($name, $value = '', $attributes = array()) {
    $this->addAttribute('class', 'button');
    parent::__construct($name, 'button', $value, $attributes);
  }
  
  public function click($jsClick, $confirm = '') {
    if ($confirm) {
      $script = sprintf("if (confirm('%s')) %s", $confirm, $jsClick);
    }
    else {
      $script = $jsClick;
    }
    $this->attributes['onclick'] = $script;
//    return $this->render(array('onclick'=>$script));
  }

    public function render($extraAttributes = array(), $replace = false) {

        //Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        //Gestion de 'laffichage ou non
        if ($this->rendered)
            return '';
        //on met a jour par rapport au portefeuille
        $this->setDroitsParPortefeuilles();

        if ($this->edit && $this->enabled) {
            $this->addResultClass();
            $result = sprintf('<button name="%s" %s>%s</button>', sprintf($this->nameFormat, $this->attributes['name']), $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']);
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                $result = sprintf('<span %s>%s</span>', $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']);
            } else {
                $result = sprintf('<span %s>&nbsp;</span>', $this->generateAttributes($extraAttributes, $replace));
            }
        }
        //On marque le champ comme rendu pour indiquer que le widget a ete rendu et eviter le rendu multiple
        $this->rendered = true;
        return $result . $this->renderInfo() . $this->renderAdminMenu();
    }
  
  
  public function goUrl($url, $confirm = '') {
    return $this->click(sprintf("goPage('%s')", $url), $confirm);
  }
  
}
?>
