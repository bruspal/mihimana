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
  @file : mmContext.php
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

class mmContext extends mmObject {

    protected
            $contextName,
            $context,
            $autoSave;

    public function __construct($contextName = '', $autoLoad = true, $autoSave = true) {
        if ($contextName == '') {
            $contextName = 'default';
        }
        $this->autoSave = $autoSave;

        $this->contextName = $contextName;
        //On charge/initialise
        if (!isset($_SESSION['__mmContext__'])) {
            $_SESSION['__mmContext__'] = array();
        }
        if (!isset($_SESSION['__mmContext__'][$contextName])) {
            $_SESSION['__mmContext__'][$contextName] = array();
        }

        if ($autoLoad == true) {
            $this->load();
        }
        return $this;
    }

    public function __destruct() {
        if ($this->autoSave) {
            if (is_array($this->context) && count($this->context) > 0) { //y'a des infos ? on sauve
                $_SESSION['__mmContext__'][$this->contextName] = $this->context;
            } else {
                unset($_SESSION['__mmContext__'][$this->contextName]);  //le context est vide ? on nettoie la session
            }
        }
    }

    public function load() {
        $this->context = $_SESSION['__mmContext__'][$this->contextName];
    }

    public function destroy() {
        $this->context = array();
    }

    public function save() {
        $_SESSION['__mmContext__'][$contextName] = $this->context;
    }

    public function get($nomVar, $default = null) {
        if (isset($this->context[$nomVar])) {
            return $this->context[$nomVar];
        } else {
            return $default;
        }
    }

    public function toArray() {
        return $this->context;
    }

    public function exists($nomVar) {
        if (isset($this->context[$nomVar])) {
            return true;
        } else {
            return false;
        }
    }

    public function set($nomVar, $valeur) {
        return $this->context[$nomVar] = $valeur;
    }

    public function remove($nomVar) {
        unset($this->context[$nomVar]);
    }

}
