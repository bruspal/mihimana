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
  @file : mmIndice.php
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

class mmIndice extends mmObject {

    protected
            $ficind;

    public function __construct($code, $date = false) {
        if ($date === false) {
            $date = date("Y-m-d");
        }
        $this->ficind = Doctrine_Core::getTable('ficind')->createQuery()->
                where("pi01 = ? AND pi02 < ?", array($code, $date))->
                orderBy('pi02')->
                execute();
        return $this;
    }

    public function getValeur() {
        if ($this->ficind === false) {
            return false;
        } else {
            return $this->ficind['pi03'];
        }
    }

}
