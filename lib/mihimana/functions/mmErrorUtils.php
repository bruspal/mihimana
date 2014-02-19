<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: functions
@file : mmErrorUtils.php
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



/*
  function mdErrorMessage($message, $titre = 'Erreur')
  {
  $resultat = sprintf('<fieldset class="mdError"><legend>%s</legend>%s<br />%s</fieldset>', $titre, $message, new mdWidgetButtonClose());
  return $resultat;
  }

  function mdAjaxError($message)
  {
  return json_encode(array('success'=>false, 'message'=>$message));
  }
 */

/**
 * Génére la chaine au format http d'un message d'erreur
 * @param string $message message a afficher
 * @param string $titre titre de la zone d'affichage (par defaut 'Erreur')
 * @return string
 */
function mmErrorMessageHttp($message, $titre = 'Erreur') {
    $resultat = sprintf('<fieldset class="mdError"><legend>%s</legend>%s<br />%s</fieldset>', $titre, $message, new mmWidgetButtonClose());
    return $resultat;
}

/**
 * Génére la chaine au format JSON d'un message d'erreur
 * @param string $message message a afficher
 * @param string $titre titre de la zone d'affichage (par defaut 'Erreur')
 * @return string
 */
function mmErrorMessageAjax($message, $titre = 'Erreur') {
    $hl = headers_list();

    return json_encode(array('success' => false, 'message' => $titre . ' : ' . $message));
}

/**
 * Génére la chaine d'un message d'erreur, renvoi du HTML si on est dans un context standard ou un JSON si on est dans un context AJAX<br />
 * ATTENTION ca reste a tester
 * @param string $message message a afficher
 * @param string $titre titre de la zone d'affichage (par defaut 'Erreur')
 * @return string
 */
function mmErrorMessage($message, $titre = 'Erreur') {
    $hl = headers_list();
    if (AJAX_RESPONSE == true) {
        mmErrorMessageAjax($message, $titre);
    } else {
        mmErrorMessageHttp($message, $titre);
    }
}

?>
