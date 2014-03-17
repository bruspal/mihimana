<?php
namespace mm\helpers\links;
/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: helpers
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

/**
 * set of function used as helpers for related links
 */

/**
 * Echoing the link to the css file
 * @param type $cssName
 * @param type $global
 */
function useCss($cssName, $global = false, $extension = '.css') {
    echo '<link rel="stylesheet" type="text/css" media="screen" href="'.asset($cssName.$extension, $global).'"></link>';
}

/**
 * Echoing the link to the interpreted sass file
 * @param type $sassName
 */
function useSass($sassName, $extension = '.scss') {
    echo '<link rel="stylesheet" type="text/css" media="screen" href="'.url("sass/$sassName.scss").'"></link>';
}
/**
 * Echoing the link to the css file
 * @param type $jsName
 * @param type $global
 */
function useJavascript($jsName, $global = false, $extension = '.js') {
    echo '<script type="text/javascript" src="'.asset($jsName.$extension, $global).'"></script>';
}
