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
@file : mdWidgetTiersPopup.php
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


class mdWidgetTiersPopup extends mmWidgetInputPopup {

  public function __construct($name, $value = '', $attributes = array(), $typeTiers = '') {
    $url = 'TiersPopup/index'.($typeTiers?'?tt='.$typeTiers.'&':'');
    parent::__construct($name, $url, '...', $value, $attributes);
    $this->addAttribute('size', '5');
  }
  
  public function render($extraAttributes = array(), $replace =  false, $extra = '') { //extra jamais utiliser juste ajouter pour que la signature de fonction soit respecte
    $extra = '';
    if ($this->getValue()) {
      $tiers = Doctrine_Core::getTable('Tiers')->find($this->getValue());
      if ($tiers) {
        $extra = $tiers['prenom_tiers'].' '.$tiers['nom_tiers'];
      }
      else {
        $this->setValue('');
      }
    }
    else {
      $this->setValue('');
    }
    return parent::render($extraAttributes, $replace, $extra);
  }
  
  public function dbClean() {
    if ($this->dbValue == 0 || $this->dbValue == null) {
      $this->attributes['value'] = '';
    }
  }
  
  public function clean() {
    parent::clean();
    if (! $this->attributes['value']) {
      $this->dbValue = 0;
    }
  }
}