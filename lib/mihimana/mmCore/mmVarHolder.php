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
     * @return \mmVarHolder
     */
    public function __construct() {
        $this->variables = array();
        return $this;
    }

    public function set($varName, $value) {
        $this->variables[$varName] = $value;
        return $value;
    }

    public function get($varName, $defaut = null) {
        if (isset($this->variables[$varName])) {
            return $this->variables[$varName];
        } else {
            return $defaut;
        }
    }

    public function remove($varName) {
        unset($this->variables[$varName]);
    }

    public function exists($varName) {
        return isset($this->variables[$varName]);
    }

    public function destroy() {
        $this->variables = array();
    }

    public function toArray() {
        return $this->variables;
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
