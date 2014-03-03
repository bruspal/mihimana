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
  @file : pWs.php
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

class pWidgetAjax extends mmProgProceduralWebService {
    //
    // TODO: transformer en appel objet standard
    //
    public function principale($action = '', $parametres = null) {
        //On traite les differentsParametre
        $ancien = error_reporting(0);
        switch ($action) {
            case 'pg': //Gestion du changement de page dans la liste
                echo $this->pagerListe($parametres);
                break;
            case 'pp':
                echo $this->popupSelectTable($parametres);
                break;
            case 'cpe': //recherche du premier enregistrement correspondant a une recherche simple
                echo $this->cherchePremierEnreg($parametres);
                break;
            case 'ccl': //retourne un tableau avec une liste d'enregistrement
                echo $this->chercheParCle($parametres);
                break;
            default:
                break;
        }
        error_reporting($ancien);
    }

    /*
     * Methodes des services web
     */

    /**
     * Genere le code html d'une page de liste
     * @param mmRequest $parametres 
     */
    public function pagerListe(mmRequest $parametres) {
        $resultat = array();
        $o = $parametres->getParam('o', false);
        $nomListe = $parametres->getParam('l', false);
        $liste = new mmWidgetRecordList($nomListe);
        //en fonction de l'ordre on met a jour la liste
        if (is_numeric($o)) {
            //c'est un nombre, on passe directement a la bonne page
            $liste->setPage($o);
        } else {
            switch ($o) {
                case 'p':
                    $liste->pagePrecedente();
                    break;
                case 's':
                    $liste->pageSuivante();
                    break;
                default:
                    break;
            }
        }
        $htmlWidget = $liste->render();

        return json_encode(array('success' => true, 'html' => $htmlWidget));
    }

    public function popupSelectTable(mmRequest $request) {
        
    }

    public function cherchePremierEnreg(mmRequest $request) {
        $table = $request->get('t', false);
        if ($table == false) {
            return json_encode(array('success' => false, 'message' => 'Table manquante'));
        }
        $cle = $request->get('c', false);
        if ($cle == false) {
            return json_encode(array('success' => false, 'message' => 'cle manquante'));
        }
        $valeur = $request->get('v', false);
        if ($valeur === false) {
            return json_encode(array('success' => false, 'message' => 'valeur manquante'));
        }
        if ($request->get('s', false) === false) {
            $select = '*';
        } else {
            $select = 'id';
        }


        $rq = Doctrine_Core::getTable($table)->createQuery()->
                select($select)->
                where("$cle = ?", $valeur)->
                fetchOne();
        //on transforme en tableau si on a trouver un enregistrement ($rq est vrai)
        if ($rq) {
            $resultatRequete = $rq->toArray();
        } else {
            $resultatRequete = false;
        }

        //on retourne le resultat
        return json_encode(array('success' => true, 'data' => $resultatRequete));
    }

    public function chercheParCle($parametres) {
        $optionsDefaut = array();
        $o = $parametres->getParam('o', '');
        $options = new mmOptions($o, $optionsDefaut);
        $nomTable = $options['table'];
        $actionCible = "goPage('" . $options['actionListe'] . "')";
        //creation de la cle de recherche
        $sqlWhere = '';
        $colonnes = explode(',', $options['cle']);
        foreach ($colonnes as $col) {
            $sqlWhere .= sprintf(" OR %s LIKE '%s%%'", $col, $options['vi']);
        }
        $sqlWhere = substr($sqlWhere, 4);
        $liste = new mmWidgetRecordList('__chercheCle__', $nomTable, $actionCible, $sqlWhere, $options);
        $html = $liste->render();
//    $html = preg_replace('/\s/', ' ', $html);
        $html = str_replace(array('<script', '</script'), array('<_script', '<_/script'), $html);
        if ($html == '') {
            $html = 'Pas de resultat';
        }
        return json_encode(array('success' => true, 'data' => $html));
    }

}