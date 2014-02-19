<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmCore
@file : mmSession.php
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
------------------------------------------------------------------------------*/


//session_start();
/**
 * Classe de gestion de la session 
 */
class mmSession extends mmObject
{
  /**
   * Initialise la session et recupere les variables 
   */
  public function __construct() {
    return $this;
  }
  
  public static function set($nomVar, $value) {
    $_SESSION[$nomVar] = $value;
    return $value;
  }
  
  public static function get($nomVar, $defaut = null) {
    if (isset($_SESSION[$nomVar])) {
      return $_SESSION[$nomVar];
    }
    else {
      return $defaut;
    }
  }
  
  public static function remove($nomVar) {
    unset($_SESSION[$nomVar]);
  }
  
  public static function exists($nomVar) {
    return isset($_SESSION[$nomVar]);
  }
  
}

?>