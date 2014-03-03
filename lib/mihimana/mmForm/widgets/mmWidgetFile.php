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
  @file : mmWidgetFile.php
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

class mmWidgetFile extends mmWidget {

    protected
            $tailleMax,
            $extension,
            $afficheBouton;

    public function __construct($name, $options = array(), $attributes = array(), $value = '') {
        parent::__construct($name, 'file', $value, $attributes, $options);
        //preparation des options
        try {
            $this->analyseOptions($options);
            $this->afficheBouton = true;
        } catch (mmExceptionControl $e) {
            mmUser::flashError($e->getMessage());
            $this->afficheBouton = false;
        }
    }

    public function postAddWidget() {
        //si ce widget est ajouté a un formulaire on modifie l'entete du formulaire contenant
        $this->containerForm->enctype = "multipart/form-data";
    }

    protected function analyseOptions($options) {
        if (isset($options['tailleMax'])) {
            $tailleMax = $options['tailleMax'];
            if (!is_numeric($tailleMax)) {
                throw new mmExceptionControl($this->getName() . ': la valeur de tailleMax doit etre un nombre entier de kilo-octet');
            }
            $tailleMax = floor($tailleMax) * 1024;
        } else {
            $tailleMax = 1024 * 1024;
        }
        $this->tailleMax = $tailleMax;
    }

    public function render($extraAttributes = array(), $replace = false) {
        $html = parent::render($extraAttributes, $replace);
        $html .= '<input type="hidden" name="MAX_FILE_SIZE" value="' . $this->tailleMax . '">';
        return $html;
    }

}