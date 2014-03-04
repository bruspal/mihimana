<?php

/* ------------------------------------------------------------------------------
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
  ------------------------------------------------------------------------------ */

class mmWidgetButton extends mmWidget {

    public function __construct($name, $value = '', $attributes = array()) {
        $this->addAttribute('class', 'button');
        parent::__construct($name, 'button', $value, $attributes);
        $this->setLabel($value);
    }

    public function click($jsClick, $confirm = '') {
        if ($confirm) {
            $script = sprintf("if (confirm('%s')) %s", $confirm, $jsClick);
        } else {
            $script = $jsClick;
        }
        $this->attributes['onclick'] = $script;
//    return $this->render(array('onclick'=>$script));
    }

    public function render($extraAttributes = array(), $replace = false) {

        if ($this->edit && $this->enabled) {
            $result = sprintf('<button name="%s" %s>%s</button>', sprintf($this->nameFormat, $this->attributes['name']), $this->generateAttributes($extraAttributes, $replace), $this->label);
            $result .= $this->renderInfo() . $this->renderAdminMenu();
        } else {
            $result = parent::render($extraAttributes, $replace);
        }
        return $result;
    }

//    public function setValue($value, $ignoreControle = 0) {
//        
//    }
    protected function generateAttributes($extraAttributes = array(), $replace = false) {
        if ($replace) {
            $attributes = $extraAttributes;
        } else {
            $attributes = array_merge($this->attributes, $extraAttributes);
        }
        //On vire les attribut 'speciaux'
        unset(
                $attributes['name'], $attributes['value']
        );

        $result = '';

        foreach ($attributes as $an => $a) {
            $result .= sprintf(' %s="%s"', $an, $a);
        }
        return substr($result, 1);
    }

//    public function goUrl($url, $confirm = '') {
//        return $this->click(sprintf("goPage('%s')", $url), $confirm);
//    }

}