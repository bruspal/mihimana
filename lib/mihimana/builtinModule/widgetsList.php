<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: builtinModule
  @file : widgetsList.php
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


/*
 * List of all widgets ordered by categories
 */
$GLOBALS['typeWidget'] = array(
    '' => '-',
    'type simple' => array(
        'text' => 'Text simple',
        'integer' => 'entier',
        'decimal' => 'Reel',
        'date' => 'Date',
        'boolean' => 'Oui/Non',
        'time' => 'Heure',
        'timestamp' => 'Timestamp',
        'list' => 'Liste de choix',
        'select' => 'Select',
        'hidden' => 'caché'
    ),
    'associé aux données' => array(
        'selectTable' => 'Select lié a Tables',
        'selectFic' => 'Select lié à un fichier',
//        'textRech'=>'Recherche sur le champ',
        'inputPopup' => 'Fenetre de recherche',
        'recordList' => 'Liste d\'enregistrements',
        'recordCle' => 'Recherche/creation sur clé',
        'imageSQL' => 'Image JPEG SQL'
    ),
    'bouton' => array(
        'button' => 'Bouton',
        'buttonClose' => 'Bouton "fermer"',
        'buttonSubmit' => 'Bouton submit',
        'buttonGoPage' => 'Bouton vers page',
        'buttonAjaxPopup' => 'Bouton popup AJAX',
        'buttonHtmlPopup' => 'Bouton popup HTML',
        'buttonGoModule' => 'Bouton vers module',
        'buttonGoModuleAjaxPopup' => 'Bouton vers module en popup ajax',
        'buttonGoModuleHtmlPopup' => 'Bouton vers module en popup HTML',
        'buttonNext' => 'Bouton enreg "suivant"',
        'buttonPrec' => 'Bouton enreg "précédent"',
        'buttonSeqNext' => 'Bouton ecran "suivant"',
        'buttonSeqPrec' => 'Bouton ecran "précédent"',
    ),
    'bloc' => array(
        'textArea' => 'Text multilignes',
        'TinyMce' => 'Editeur TinyMce',
        'CKEditor' => 'Editeur CKEditor',
        'blob' => 'Blob binaire',
        'clob' => 'Blob Text'
    ),
    'interface' => array(
        'menu' => 'Menu',
    ),
    'dynamique' => array(
        'execScreen' => 'Ecran externe',
        'execModule' => 'Programme Maides',
        'execProg' => 'Programme externe'
    )
);