<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmCore
  @file : mmOptions.php
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



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mdOptions
 *
 * @author bruno
 */
class mmOptions extends mmVarHolder {

    public function __construct($chaineOptions, $defaut = array()) {
        if (is_array($chaineOptions)) {
            $this->variables = $chaineOptions;
        } else {
            $this->variables = mmParseOptions($chaineOptions, $defaut);
        }
    }

    public function get($nomOption, $default = null) {
        if (isset($this->variables[$nomOption])) {
            return $this->variables[$nomOption];
        } else {
            if ($default === null) {
                if (isset($this->variables['default'])) {
                    return $this->variables['default'];
                } else {
                    return '';
                }
            } else {
                return $default;
            }
        }
    }

    public function offsetGet($nomOption) {
        if (isset($this->variables[$nomOption])) {
            return $this->variables[$nomOption];
        } else {
            if (isset($this->variables['default'])) {
                return $this->variables['default'];
            } else {
                return '';
            }
        }
    }

    public function getTableau() {
        return $this->variable;
    }

    public function getChaine($urlEncode = true) {
        $result = '';
        foreach ($this->variables as $key => $valeur) {
            $result .= ";$key=$valeur";
        }
        $result = $urlEncode ? urlencode(substr($result, 1)) : substr($result, 1);
        return $result;
    }

    public function setIfVide($nomVar, $valeur) {
        if (!isset($this->variables[$nomVar])) {
            return $this->set($nomVar, $valeur);
        }
    }

}
