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
  @file : mmHelperLinks.php
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
 * Echoing the tag to the css file
 * @param string $cssName css filename without extension
 * @param boolean $global
 * @param string $extension filename extension, default .css
 */
function renderCss($cssName, $global = false, $extension = '.css') {
    echo useCss($cssName, $global, $extension);
}

/**
 * return the tag to the css file
 * @param string $cssName css filename without extension
 * @param boolean $global
 * @param string $extension filename extension, default .css
 * @return string
 */
function useCss($cssName, $global = false, $extension = '.css') {
    return '<link rel="stylesheet" type="text/css" media="screen" href="'.useAsset($cssName.$extension, $global).'"></link>';
}



/**
 * Echoing the tag to the interpreted sass file, sass file root is located in assets/scss directory
 * @param string $sassName sass filename
 * @param string $extension file extension, default '.scss'
 */
function renderSass($sassName, $extension = '.scss') {
    echo useSass($sassName, $extension);
}

/**
 * return the tag to the interpreted sass file, sass file root is located in assets/scss directory
 * @param string $sassName sass filename
 * @param string $extension file extension, default '.scss'
 * @return string
 */
function useSass($sassName, $extension = '.scss') {
    return '<link rel="stylesheet" type="text/css" media="screen" href="'.url("sass/$sassName.scss").'"></link>';
}


/**
 * return the tag to the javascript file
 * @param string $jsName script name (without extension)
 * @param boolean $global if false (default) the referenced javascript will be in the assets projetx directory
 * @param string $extension the file extension, by default '.js'
 */
function renderJavascript($jsName, $global = false, $extension = '.js') {
    echo useJavascript($jsName, $global, $extension);
}

/**
 * Echoing the tag to the javascript file
 * @param string $jsName script name (without extension)
 * @param boolean $global if false (default) the referenced javascript will be in the assets projetx directory
 * @param string $extension the file extension, by default '.js'
 * @return string
 */
function useJavascript($jsName, $global = false, $extension = '.js') {
    return '<script type="text/javascript" src="'.useAsset($jsName.$extension, $global).'"></script>';
}

/**
 * Echoing the link tag to $url and labeled $label. Extra parameters can be provided via the array (attrName=>attrValue, ...) $extraParams
 * @param string $url
 * @param string $label
 * @param array $extraParams
 * @return string
 */
function renderLink($url, $label = '#', $extraParams= array()) {
    echo useLink($url, $label, $extraParams);
}

/**
 * Return the string of an link tag to $url and labeled $label. Extra parameters can be provided via the array (attrName=>attrValue, ...) $extraParams
 * @param string $url
 * @param string $label
 * @param array $extraParams
 * @return string
 */
function useLink($url, $label = '#', $extraParams = array()) {
    $strParam = '';
    if ($extraParams) {
        $extraParams = (array)$extraParams;
        foreach($extraParams as $attr => $value) {
            $strParam .= " $attr=\"$value\"";
        }
    }
    return '<a href="'.url($url).'"'.$strParam." >$label</a>";
}