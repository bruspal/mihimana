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
  @file : mmWidgetTextArea.php
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

class mmWidgetTextArea extends mmWidget {

    public function __construct($name, $value = '', $attributes = array()) {
        $this->addAttribute('autocomplete', 'off');
        $this->addAttribute('class', 'textarea');
        parent::__construct($name, 'text', $value, $attributes);
    }

    public function render($extraAttributes = array(), $replace = false) {

        //Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        $this->rendered = true;
        if ($this->edit && $this->enabled) {
            $this->addResultClass();
            return sprintf('<textarea type="%s" name="%s" %s>%s</textarea>', $this->attributes['type'], sprintf($this->nameFormat, $this->attributes['name']), $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']) . $this->renderInfo() . $this->renderAdminMenu();
            ;
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                return sprintf('<span %s>&nbsp;</span>', $this->generateAttributes($extraAttributes, $replace)) . $this->renderAdminMenu();
                ;
            } else {
                return '';
            }
        }
    }

    /**
     * return iner tags string, /!\ class attributes will be removed
     * @return type
     */
    public function useAttrs() {
        $attributes = $this->attributes;
        unset($attributes['class']);
        unset($attributes['value']);
        return sprintf('type="%s" name="%s"', $this->attributes['type'], sprintf($this->nameFormat, $this->attributes['name'])).$this->generateAttributes($attributes, true);
    }


}