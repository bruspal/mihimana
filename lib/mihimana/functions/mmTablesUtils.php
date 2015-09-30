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
  @file : mmTablesUtils.php
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
 * This function return an array of key=>value from the parameters table.
 *
 * @param string $category table name coma separated
 * @param string $group group name (optionnal)
 * @return array
 */
function mmTableCategorie($category = false, $group = false) {
    $query = "SELECT * FROM tables WHERE id_table IN ('".implode("','",(array)$category)."')".($group ? " AND groupe = '".$group."'" : "");
    $result = mmSQL::query($query);
    return $result;
}

/**
 * @param string $categorie nom de la catégorie cherchée, boolean $withNom ajouter l'ID dans le libellé (dafault true)
 * @return array() sous la forme ("nom" => "valeur",...) pour tous les resultat
 *
 */
function mmTableCategorieCle($categorie, $withNom = TRUE, $addEmpty = false, $groupe = '') {
    $q = mmTableCategorie($categorie);
    $result = array();
    if ($addEmpty) {
        $result[''] = '';
    }
    foreach ($q as $cat) {
        $result[$cat['nom']] = ($withNom ? $cat['nom'] . ' - ' : '') . $cat['valeur'];
    }
    return $result;
}

function mmTableCategorieDetail($categorie, $withNom = TRUE, $addEmpty = false, $groupe = '') {
    $q = mmTableCategorie($categorie);
    $result = array();
    if ($addEmpty) {
        $result[''] = '';
    }
    foreach ($q as $cat) {
        $result[$cat->getNom()]['valeur'] = ($withNom ? $cat->getNom() . ' - ' : '') . $cat->getValeur();
        $result[$cat->getNom()]['libre1'] = $cat['libre1'];
        $result[$cat->getNom()]['libre2'] = $cat['libre2'];
    }
    return $result;
}

function mmTableParametre($categorie, $nomParam) {
    $resultat = mmSQL::queryOne('SELECT * FROM tables WHERE id_table = ? AND nom = ?', array($categorie, $nomParam));
    return $resultat;

}