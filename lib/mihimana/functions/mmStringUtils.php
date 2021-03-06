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
  @file : mmStringUtils.php
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
 * Slugify a string
 * @param string $string the string
 * @return string the slugified string
 */
function strSlugify($string) {
    $result = html_entity_decode($string, ENT_NOQUOTES);
//  $result = strtolower($result);
    //TODO remplacer str_replace par preg_replace
    $result = str_replace(array(' ', '-', '\'', '"', '\\', '/', '?', '!', '#', '@', '$', '%', '^', '[', ']', '__'), '_', $result);
    $result = str_replace(array('.', ',', '\'', '"', ':', ';'), '', $result);
    return $result;
}

function generateRandomString($nbrCaracteres = 20) {
    //creation de la chaine de hashage
    $caracteresUtilisable = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmanopqrstuvwxyz0123456789";
    $nbrCaracteresDisponible = strlen($caracteresUtilisable) - 1;
    $chaineResultat = '';
    for ($i = 0; $i < $nbrCaracteresDisponible; $i++) {
        $position = rand(0, $nbrCaracteresDisponible);
        $caractere = $caracteresUtilisable[$position];
        $chaineResultat .= $caractere;
    }
    return $chaineResultat;
}

function stringToArray($chaine) {
    $resultat = array();
    $chaine = str_replace(';', "\n", $chaine);
    if ($chaine != '') {
        $lignes = explode("\n", $chaine);
        foreach ($lignes as $ligneCourante) {
            list($cle, $valeur) = explode(":", str_replace("\r", '', $ligneCourante));
            $resultat[$cle] = $valeur;
        }
    }
    return $resultat;
}

function strpos_multi($chaine, $listChar, $start = 0) {
    $result = strcspn($chaine, $listChar, $start);
    if ($result != strlen($chaine)) {
        return $result;
    }
    return false;
}

function getRawSql($query) {
    if (!($query instanceof Doctrine_Query)) {
        throw new Exception('Not an instanse of a Doctrine Query');
    }

    $query->limit(0);

    if (is_callable(array($query, 'buildSqlQuery'))) {
        $queryString = $query->buildSqlQuery();
        $query_params = $query->getParams();
        $params = $query_params['where'];
    } else {
        $queryString = $query->getSql();
        $params = $query->getParams();
    }

    $queryStringParts = split('\?', $queryString);
    $iQC = 0;

    $queryString = "";

    foreach ($params as $param) {
        if (is_numeric($param)) {
            $queryString .= $queryStringParts[$iQC] . $param;
        } elseif (is_bool($param)) {
            $queryString .= $queryStringParts[$iQC] . $param * 1;
        } else {
            $queryString .= $queryStringParts[$iQC] . '\'' . $param . '\'';
        }

        $iQC++;
    }
    for ($iQC; $iQC < count($queryStringParts); $iQC++) {
        $queryString .= $queryStringParts[$iQC];
    }

    return $queryString;
}

function preg_pos($patterne, $subject, $offset) {
    $trouve = preg_match($patterne, $subject, $fragments, PREG_OFFSET_CAPTURE, $offset);
    if ($trouve) {
        return $fragments[0][1];
    } else {
        return false;
    }
}

function mdTrimNL($source) {
    $cible = str_replace(array('<br>', '<br/>', '<br />'), "\x87", $source);
    $cible = trim($cible, "\n\r\x87");
    $cible = str_replace("\x87", '<br />', $cible);

    return $cible;
}