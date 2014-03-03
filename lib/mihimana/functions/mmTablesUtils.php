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

function mmTableCategorie($categorie = '', $groupe = '') {
    $result = Doctrine_Core::getTable('Tables')->createQuery('t');
    if ($categorie !== '') {
        if (is_string($categorie)) {
            $categorie = array($categorie);
        }
        $result = $result->whereIn('t.id_table', $categorie);
    } else {
        $result = $result->where('1 = 0');
    }
    if ($groupe) {
        if (is_string($groupe)) {
            $groupe = array($groupe);
        }
        $result = $result->wherIn('t.groupe', $groupe);
    }

    $result = $result->orderBy('t.nom')->execute();
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