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
  
  public function goUrl($url, $confirm = '') {
    return $this->click(sprintf("goPage('%s')", $url), $confirm);
  }
  
}
?>
