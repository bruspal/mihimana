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
  @file : mmVarHolder.php
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

class mmVarHolder extends mmObject implements ArrayAccess {

    protected $variables;

    /**
     * Generic variable holder, implementing ArrayAccess
     * @param mixed $variables initial set off variable
     * @return mmVarHolder
     */
    public function __construct($variables = array()) {
        if ( ! is_array($variables)) {
            throw new mmExceptionDev("mmVarHolder : provided data must be an array");
        }
        $this->variables = $variables;
        return $this;
    }

    /**
     * Insert or replace $varName with the $value
     * @param string $varName
     * @param mixed $value
     * @return mixed return $value
     */
    public function set($varName, $value) {
        $this->variables[$varName] = $value;
        return $value;
    }

    /**
     * return $Varname, if not found return $default
     * @param string $varName
     * @param mixed $default if ommitted $default = null
     * @return mixed
     */
    public function get($varName, $default = null) {
        if (isset($this->variables[$varName])) {
            return $this->variables[$varName];
        } else {
            return $default;
        }
    }

    /**
     * Unset $varName
     * @param string $varName
     */
    public function remove($varName) {
        unset($this->variables[$varName]);
    }

    /**
     * Return if $varName exists, otherwise false
     * @param string $varName
     * @return boolean
     */
    public function exists($varName) {
        return isset($this->variables[$varName]);
    }

    /**
     * Empty var holder
     */
    public function destroy() {
        $this->variables = array();
    }

    /**
     * Return var holder content as an array
     * @return array
     */
    public function toArray() {
        return $this->variables;
    }

    /**
     * return true if var holder is empty
     * @return boolean
     */
    public function isEmpty() {
        return count($this->variables) == 0;
    }
    /*
     * Method pour l'acces de type array
     * ceci permet de gerer les parametres comme un tableau
     */

    public function offsetGet($offset) {
        if (isset($this->variables[$offset])) {
            return $this->variables[$offset];
        } else {
            throw new mmExceptionDev("le parametre $offset n'existe pas");
        }
    }

    public function offsetSet($offset, $value) {
        $this->variables[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->variables[$offset]);
    }

    public function offsetExists($offset) {
        return isset($this->variables[$offset]);
    }

}
