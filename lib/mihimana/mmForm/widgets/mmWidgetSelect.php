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
  @file : mmWidgetSelect.php
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

class mmWidgetSelect extends mmWidget {

    protected $values;

    /**
     * Create a new select widget
     * @param mixed $name internal name (if string) or other widget instance to get select based on provided widget
     * @param array $values associative array : value => label
     * @param mixed $value widget value
     * @param array $attributes associative array of extra attributes
     */
    public function __construct($name, $values = array(), $value = '', $attributes = array()) {
        $this->addAttribute('class', 'select');
        $this->values = $values;
        parent::__construct($name, 'select', $value, $attributes);
        //Pour assurer de bien etre dans une combo on supprime l'eventuel attribut size
        if (get_class($this) == 'mmWidgetSelect') {
            unset($this->attributes['size']);
        }
    }

    public function render($extraAttributes = array(), $replace = false) {

        if ($replace) {
            $attributes = $extraAttributes;
        } else {
            $attributes = array_merge($this->attributes, $extraAttributes);
        }
        $result = '';
        if ($this->edit && $this->enabled) {
            $result = sprintf('<select name="%s" %s>', sprintf($this->nameFormat, $this->attributes['name']), $this->generateAttributes($extraAttributes, $replace));
            $result .= $this->computeSelectValues($this->values, $this->attributes['value']);
            $result .= '</select>';
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                $result = sprintf('<span %s>%s</span>', $this->generateAttributes($extraAttributes, $replace), isset($this->values[$this->attributes['value']]) ? $this->values[$this->attributes['value']] : $this->attributes['value']);
            } else {
                $result = '';
            }
        }
        return $result . $this->renderInfo() . $this->renderAdminMenu();
    }

    protected function computeSelectValues($values, $value) {
        $result = '';
        foreach ($values as $key => $val) {
            if (is_array($val)) {
                $result .= sprintf('<optgroup label="%s">%s</optgroup>', $key, $this->computeSelectValues($val, $value));
            } else {
                if ((string) $key == (string) $value) {
                    $result .= sprintf('<option value="%s" selected="selected">%s</option>', $key, $val);
                } else {
                    $result .= sprintf('<option value="%s">%s</option>', $key, $val);
                }
            }
        }
        return $result;
    }

}