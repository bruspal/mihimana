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

function url_for($url = false) {
    if ($url) {
        
    }
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

/**
 * Envois via apache les entetes par defaut necessaire au fonctionnement du programme coté client. Attention cette fonction doit etre appelé dans le head du fond d'ecran de l'application
 */
function mdHtmlHeaderStandard() {
    ?>
    <link rel="stylesheet" type="text/css" media="screen" href="css/menu.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/maides.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/jquery.ui.css" />
    <link rel="stylesheet" href="js/codeMirror/lib/codemirror.css">
    <link rel="stylesheet" href="js/codeMirror/lib/custom.css">
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>
    <script type="text/javascript" src="js/jqModal.js"></script>
    <script type="text/javascript" src="js/ckeditor/ckeditor.js"></script>
    <script src="js/codeMirror/lib/codemirror.js"></script>
    <script src="js/codeMirror/mode/javascript/javascript.js"></script>
    <script type="text/javascript" src="js/maides.js"></script>
    <?php
}