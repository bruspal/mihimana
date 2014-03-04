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
  @file : mmWidgetButtonSubmit.php
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

class mmWidgetButtonSubmit extends mmWidgetButton {

    protected $preSubmit;

    /**
     * submit button widget
     * @param type $label : submit button's label (Default 'OK')
     * @param type $preSubmit : javascript should be executed before perform submit
     * @param type $name : the widget name
     * @param type $attributs : extra attributes
     */
    public function __construct($label = 'OK', $preSubmit = '', $name = '', $attributs = array()) {
        if (!$name) {
            $name = strSlugify(strtolower($label));
        }
        if ($preSubmit) {
            $this->preSubmit = $preSubmit . ';';
        } else {
            $this->preSubmit = '';
        }
        parent::__construct($name, $label, $attributs);
    }

    public function render($extraAttributes = array(), $replace = false) {
        // Dans ce cas la on effectue l'affectation du javascript au moment du rendu car dans le constructeur on ne connais pas encore le containerForm
        if (AJAX_REQUEST) {
            $parameters = array('onclick' => sprintf("%smmAjxSubmit($('form#%s'))", $this->preSubmit, $this->containerForm->getId()));
        } else {
            $parameters = array('onclick' => sprintf("%ssubmit()", $this->preSubmit));
//            $parameters = array('type' => 'submit');
        }
        $this->addAttributes($parameters);
        //On effectue maintenant le rendu normal
        return parent::render($extraAttributes, $replace);
    }

}