<?php
/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: functions
  @file : mmUrlUtils.php
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
 * Format the input URL to the correct format regarding mihimana.<br>
 * if $url is absolute, nothing will be changed while relatives will be adapted to internal mihimana url<br>
 * There is some reserved words, all of them starts with '@'
 * <ul>
 * <li>@home : Url for home page</li>
 * <li>@module :  the current module</li>
 * <li>@action : the current action </li>
 * </ul>
 * 
 * 
 * @param string $url the input url
 * @return string the well formated URL
 */
function url($url) {
    
    $url = str_replace(array('@module', '@action'), array(MODULE_COURANT, ACTION_COURANTE), $url);
    switch ($url) {
        case '@home':
            return $_SERVER['SCRIPT_NAME'];
            //return WEB_CONTEXT;
            break;

        default:
            if (preg_match('#https?://#', $url)) {
                return $url;
            }
            return $_SERVER['SCRIPT_NAME'].'/'.$url;
            break;
    }
}

function assets($url, $global = false) {
    return $global ? WEB_CONTEXT.'/'.$url : WEB_CONTEXT.'/'.APPLICATION.'_assets/'.$url;
}

/**
 * Retourne l'url encodé. Si jamais on est en debug y'a pas de debug
 * @param type $url
 * @return type
 * @throws mmExceptionControl 
 */
function genereUrlProtege($url) {
    if (true || DEBUG) { //desactivé pour le moment
        //En mode debug on encode pas les URL
        return $url;
    }
    $cleHashage = generateRandomString();
    $tableauCleDeHashage = mmSession::get('__tableauUrls__', array());
    if (isset($tableauCleDeHashage[$cleHashage])) {
        throw new mmExceptionControl('La cle de hashage d\'url existe deja dans le tableau des urls.');
    } else {
        $tableauCleDeHashage[$cleHashage] = $url;
        mmSession::set('__tableauUrls__', $tableauCleDeHashage);
        return '?ucah=' . $cleHashage;
    }
}

/**
 * Effectue un redirect vers $url, si protegeUrl est a vrai (defaut) encode l'url avant
 * @param type $url
 * @param type $protegeUrl 
 */
function redirect($url, $protegeUrl = true) {
    if ($protegeUrl) {
        $url = genereUrlProtege($url);
    }
    //dans un context ajax pour le moment on interdit la redirection, la redirection est explicitement fait par le javascript mdAjxSubmit()
    //TODO: a voir comment gerer ca dans le futur
    if (true || !AJAX_REQUEST) {
        ob_clean(); //on vide le buffer de sortie au cas ou pour eviter les erreurs
        header("HTTP/1.1 302 Found");
        header("Location: $url");
        exit();
    }
}