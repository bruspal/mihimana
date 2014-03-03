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
  @file : mmWidgetButtonClose.php
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

class mmWidgetButtonClose extends mmWidgetButton {

    public function __construct($libelle = 'Fermer', $name = '', $attributes = array()) {
        if (!$name) {
            $name = str_replace(' ', '_', lcfirst($libelle));
        }
        //on definie le type d'appel pour effectuer le bon appel, soit on rafraichis le navigateur
        //soit on rafraichis la dialoguebox
        if (AJAX_REQUEST) {
            //C'est un appel ajax, on met a jour la sous fenetre
            $attributes['onclick'] = "$('#__mdDialog').jqmHide()";
        } else {
            $attributes['onclick'] = "window.close()";
        }
        parent::__construct($name, $libelle, $attributes);
    }

}