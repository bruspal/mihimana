<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmCore
  @file : mmSQL.php
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

class mmSQL extends mmObject {

    public static function requete($requete, $qm = null) {
        return self::query($requete, $qm);
    }

    public static function query($query, $params = array(), $fetchMode = PDO::FETCH_BOTH, $qm = null) {
        if ($qm == null) {
            $qm = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        return $qm->execute($query, $params)->fetchAll($fetchMode);
    }
    
    public static function queryOne($query, $params = array(), $fetchMode = PDO::FETCH_BOTH, $qm = null) {
        if ($qm == null) {
            $qm = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        return $qm->execute($query, $params)->fetch($fetchMode);
    }
    /**
     * Quick qnd dirty method to execute native SQL
     * @param string $query
     * @param ressource $conn mysql connection
     */
    public static function _query($query, $conn = null) {
        
        $conn = new PDO('mysql:host='.MMSQL_HOST.';dbname='.MMSQL_DB.';charset=utf8', MMSQL_USER, MMSQL_PASSWD);
        $stmt = $conn->query($query);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = array();
        foreach($stmt->fetch() as $ligne) {
            $result[] = $ligne;
        }
        return $result;
    }

    /**
     * Genere une chaine de caractere codant la cle unique de l'enregistrement
     * @param Doctrine_Record $enregistrement
     * @return string 
     */
    public static function genereChaineIndex(Doctrine_Record $enregistrement, $encodeUrl = true, $separateur = '+') {
        $chaineResultat = '';
        $indexTable = $enregistrement->identifier(); //retourne sous forme d'un tableau $nomIndex=>$valeurIndex
        foreach ($indexTable as $nomIndex => $valeurIndex) {
            $chaineResultat .= "$separateur$nomIndex=$valeurIndex";
        }
        //on retire le premier '&' et on renvoie
        $chaineResultat = $encodeUrl ? urlencode(substr($chaineResultat, 1)) : substr($chaineResultat, 1);
        return $chaineResultat;
    }

    /**
     * Retourne le tableau correspondant a la $chaineIndex fournie
     * @param type $chaineIndex
     * @param type $separateur
     * @return type 
     */
    public static function genereIndex($chaineIndex, $separateur = '+') {
        $resultat = array();
        $temp = explode($separateur, $chaineIndex);
        foreach ($temp as $colonne) {
            list($nomCol, $valeur) = explode('=', $colonne);
            $resultat[$nomCol] = $valeur;
        }

        return $resultat;
    }

    /**
     * retourne les champs qui compose la cle unique
     * 
     * @param Doctrine_Table $table 
     * @param si $chane = true renvoie le resultat sous forme de chaine, sous forme de tableau sinon
     */
    public static function getCleUnique($table, $chaine = false) {
        if (is_string($table)) {
            $table = Doctrine_Core::getTable($table);
        }

        $cle = (array) $table->getIdentifier();
        if ($chaine) {
            $cleChaine = implode(',', $cle);
            return $cleChaine;
        } else {
            return $cle;
        }
    }

}
