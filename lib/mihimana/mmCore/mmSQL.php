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


    /**
     * Execute a query, result is returned as an array of records
     * @param string $query query string
     * @param array $params array of query variables
     * @param int $fetchMode PDO constant to result's format
     * @param type $connexion PDO connexion
     * @return array
     */
    public static function query($query, $params = array(), $fetchMode = PDO::FETCH_ASSOC, $connexion = null) {
        if ($connexion == null) {
            $connexion = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        return $connexion->execute($query, $params)->fetchAll($fetchMode);
    }

    /**
     * Execute a query, return the first result as PDO record
     * @param string $query query string
     * @param array $params array of query variables
     * @param int $fetchMode PDO constant to result's format
     * @param type $connexion PDO connexion
     * @return array
     */
    public static function queryOne($query, $params = array(), $fetchMode = PDO::FETCH_ASSOC, $qm = null) {
        if ($qm == null) {
            $qm = Doctrine_Manager::getInstance()->getCurrentConnection();
        }
        return $qm->execute($query, $params)->fetch($fetchMode);
    }

    /**
     * Execute a query, result is returned as a JSON string containing list of records
     * @param string $query query string
     * @param array $params array of query variables
     * @param int $fetchMode PDO constant to result's format
     * @param type $connexion PDO connexion
     * @return string
     */
    public static function queryJSON($query, $params = array(), $fetchMode = PDO::FETCH_ASSOC, $connexion = null) {
        $resultArray = self::query($query, $params, $fetchMode, $connexion);
        return mmJSON::sendJSON($resultArray);
    }

    /**
     * Execute a query, result is returned as a JSON string containing the first record
     * @param string $query query string
     * @param array $params array of query variables
     * @param int $fetchMode PDO constant to result's format
     * @param type $connexion PDO connexion
     * @return array
     */
    public static function queryOneJSON($query, $params = array(), $fetchMode = PDO::FETCH_ASSOC, $connexion = null) {
        $resultArray = self::queryOne($query, $params, $fetchMode, $connexion);
        return mmJSON::sendJSON($resultArray);
    }

    public static function execute($query, $params = array()) {
        $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
//        return $stmt->fetchAll();
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
