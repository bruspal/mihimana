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
  @file : mmWidgetSelectTable.php
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

class mmWidgetSelectTable extends mmWidgetSelect {

    protected $values;
    protected $table;

    public function __construct($name, $table, $value = '', $attributes = array()) {

        $values = mmTableCategorieCle($table);
        $this->table = $table;

        parent::__construct($name, $values, $value, $attributes);
        if (get_class($this) == 'mmWidgetSelectTable') {
            unset($this->attributes['size']);
        }
    }

    public function render($extraAttributes = array(), $replace = false) {
        $result = parent::render($extraAttributes, $replace);
        return $result . $this->popupTable();
    }

    public function popupTable() {
        if (mmUser::superAdmin()) {
            return sprintf('<input type="button" style="font-size: 8px; width: 15px; padding: 0px; opacity: 0.4; position: obsolute; background-color: #FF8F00;" value="#" id="et_%s" style="min-width: 20px;" onclick="openWindow(\'%s\', \'table\')" ></input>', $this->getId(), url('pSelectTablePopup?id=' . $this->table));
//      return sprintf('<input type="button" value="#" id="et_%s" style="min-width: 20px;" onclick="alert(\'a implementer\')"', $this->getId());
        } else {
            return '';
        }
    }

}