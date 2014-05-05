<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm/AJsWidgets
  @file : mmAJsWidgetButton.php
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

class mmAJsWidgetButton extends mmWidgetButton {
    /**
     * Create a new mmAJsWidgetButton instance
     * @param string $name internal name
     * @param string $label button label
     * @param string $jsClick javascript attached to the 'ng-click' event
     * @param array $attributes extra attributes
     */
    public function __construct($name, $label = '', $jsClick = '', $attributes = array()) {
        parent::__construct($name, $label, $attributes);
        if ($jsClick) {
            $this->click($jsClick);
        }
    }

    public function click($jsClick) {
        $this->attributes['ng-click'] = $jsClick;

    }
}
