<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm/widgets
  @file : mmWidgetRecordList.php
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

class mmWidgetRecordList extends mmWidget {

    protected
            $nomTable,
            $sqlWhere,
            $options,
            $nbTotalElement,
            $listColonnes,
            $collection,
            $actionCible,
            $firstCall,
            $colonnesTable,
            $order,
            $retour,
//variables de parametres d'affichage          
            $cols,
            $nbElement,
            $page,
            $pageCourante,
            $nbrPages,
            $largeurs,
//gestion des pages          
            $context;

    /**
     * <b>$name :</b> nom system du widget, si c'est le seul parametre fournis, va rechercher le contexte associé a $name. Sinon c'est un parametrage du widget complet qui sera stocké dans un context qui lui est propre<br />
     * <b>$actionCible :</b> que faire quant une ligne est cliqué. Au format 'actions_ecrite(%s)' ou %s contiens l'index du fichier<br /> 
     * Les options sont:
     * <ul>
     * <li><b>lines :</b> nombre de resultats par page (par defaut 10)</li>
     * <li><b>cols :</b>list des champs affiché sous forme d'un tableau au format array('nom_colonne_table' et/ou 'nom_colonne_table'=>'libellé colonne'). Si omis prend toutes les colonnes</li>
     * <li><b>urlChgPage :</b>url du code de changement de page. Par defaut pWidgetAjax. au format ?module=pWidgetAjax&action=%s</li>
     * <li><b> :</b></li>
     * </ul>
     * @param type $name
     * @param type $nomTable
     * @param type $cols
     * @param type $sqlWhere
     * @param type $options 
     */
    public function __construct($name, $nomTable = '', $actionCible = '', $sqlWhere = '', $options = array()) {
        parent::__construct($name, '');
        $this->addAttribute('class', 'list');

        $this->context = new mmContext('recordList_' . $name);

        if ($nomTable != '') { //Si on founis un nom de table on considere que c'est un widget normal, on le construit completement
            $this->nomTable = $nomTable;
            if ($sqlWhere != '') {
                $this->sqlWhere = $sqlWhere;
            } else {
                $this->sqlWhere = '1';
            }
            if ($options instanceof mmOptions) {
                $options = $options->toArray();
            }
            $this->options = $options;
            $this->actionCible = $actionCible;
            //On compte le nombre d'element total de la liste
            $rq = Doctrine_Query::create()->select('count(*) as nbRec')
                    ->from($nomTable)
                    ->where($this->sqlWhere)
                    ->fetchArray();
            $this->nbTotalElement = $rq[0]['nbRec'];
            $this->pageCourante = 1;
            $this->firstCall = true;
            //on prepare le context pour l'appel aux eventuel pagination
            $this->context->destroy();
            $this->context->set('nomTable', $this->nomTable);
            $this->context->set('sqlWhere', $this->sqlWhere);
            $this->context->set('options', $this->options);
            $this->context->set('actionCible', $this->actionCible);
            $this->context->set('nbTotalElement', $this->nbTotalElement);
            $this->context->set('pageCourante', $this->pageCourante);
        } else { //Sinon si on ne fournis que le nom on va recuperer dans la session les info utile
            $this->nomTable = $this->context->get('nomTable');
            $this->sqlWhere = $this->context->get('sqlWhere');
            $this->options = $this->context->get('options');
            $this->actionCible = $this->context->get('actionCible');
            $this->nbTotalElement = $this->context->get('nbTotalElement');
            $this->pageCourante = $this->context->get('pageCourante');
            $this->firstCall = false;
        }
        //On analyse les options fournis et met a jour $this->options
        $this->colonnesTable = Doctrine_Core::getTable($this->nomTable)->getColumnNames();
        $this->parseOptions();
        $this->nbrPages = ceil($this->nbTotalElement / $this->nbElement); //nombre total de page
    }

    public function setPage($numPage) {
        if ($numPage <= $this->nbrPages) {
            $this->pageCourante = $numPage;
            $this->context->set('pageCourante', $this->pageCourante);
        }
    }

    public function pageSuivante() {
        $this->setPage($this->pageCourante + 1);
    }

    public function pagePrecedente() {
        $this->setPage($this->pageCourante - 1);
    }

    public function render($extraAttributes = array(), $replace = false) {
        //Preparation du tableau des largeurs
        //création du html pour le widget
        if ($this->rendered)
            return '';  //On authorise qu'un seule et unique rendue du widget par page. Si il est marqué comme deja rendue, retour une chaine vide
            
//Preparation des options
        $this->generateCollection();

        if (count($this->collection) == 0) {
            return '';
//<---- Sortie ici. Si on a d'enregistrement on renvoie une chaine vide. cad le widget n'est pas generé      
        }
        //Generation de l'entete
        $header = '<thead><tr>';
        $indiceLargeur = 0;
        $largeurTotale = 0;
        foreach ($this->listColonnes as $libelleCol) {
            if (isset($this->largeurs[$indiceLargeur])) {
                $header .= sprintf('<th style="width: %sex">%s</th>', $this->largeurs[$indiceLargeur], $libelleCol);
            } else {
                $header .= "<th>$libelleCol</th>";
            }
            $largeurTotale = $largeurTotale + $this->largeurs[$indiceLargeur];
            $indiceLargeur++;
        }
        $header .= "</tr></thead>";
        //generation du corp
        $body = '<tbody>';
        foreach ($this->collection as $enregistrement) {
            //On affiche chaque ligne
            if ($this->retour) {
                $valeurARetourner = $enregistrement[$this->retour];
            } else {
                $valeurARetourner = mmSQL::genereChaineIndex($enregistrement);
            }
            $ligne = sprintf('<tr onclick="%s">', sprintf($this->actionCible, $valeurARetourner));
            $faisLien = true;
            foreach ($this->listColonnes as $colonne => $varPasUtilisee) {
                $nomColonne = mmParseSqlConcat($colonne, $this->colonnesTable, true); // str_replace(array('+', ' '), '_', $colonne);
                if ($faisLien) {
                    //Y'a un comportement a revoir ici: $this->actionCible peut contenir du javascript
                    $ligne .= sprintf('<td><a href="#" onclick="%s">%s&nbsp;</a></td>', sprintf($this->actionCible, $valeurARetourner), $enregistrement[$nomColonne]);
                    $faisLien = false;
                } else {
                    $ligne .= sprintf('<td>%s&nbsp;</td>', $enregistrement[$nomColonne]);
                }
            }
            $ligne .= '</tr>';
            $body .= $ligne;
        }
        $body .= '</tbody>';
        //generation du footer (si besoin)
        $footer = '';

        //resultat final
        //Doit on rajouter le pager ?
        if ($this->nbrPages > 1) {
            $boutonPrec = new mmWidgetButton($this->getName() . '_prev', '&lt;', array('onclick' => "pageRecordListe('" . $this->getName() . "', '" . $this->getId() . "', 'p')"));
            $boutonSuiv = new mmWidgetButton($this->getName() . '_next', '&gt;', array('onclick' => "pageRecordListe('" . $this->getName() . "', '" . $this->getId() . "', 's')"));
            if ($this->pageCourante <= 1) {
                $boutonPrec->addAttribute('disabled', 'disabled');
                $scriptPrec = '';
            }
            if ($this->pageCourante >= $this->nbrPages) {
                $boutonSuiv->addAttribute('disabled', 'disabled');
                $scriptSuiv = '';
            }
            $htmlNavigation = '<div class="list_navigation"><span class="info_pages">' . $this->pageCourante . '/' . $this->nbrPages . '</span>' . $boutonPrec . $boutonSuiv . '</div>';
        } else {
            $htmlNavigation = '';
            $scriptPrec = '';
            $scriptSuiv = '';
        }

        if ($largeurTotale == 0) {
            $largeurTotale = '100%';
        } else {
            $largeurTotale .= 'ex';
        }

        if ($this->firstCall) {
            //Si on est dans un context de la creation du widget on genere le html avec le container
            $result = sprintf('<div id="%s"><table class="%s" style="width: %s">%s%s%s</table>%s</div>', $this->getId(), $this->getAttribute('class'), $largeurTotale, $header, $body, $footer, $htmlNavigation);
        } else {
            //sinon on met a jour que le contenu
            $result = sprintf('<table class="%s" style="width: %s">%s%s%s</table>%s', $this->getAttribute('class'), $largeurTotale, $header, $body, $footer, $htmlNavigation);
        }

        //marquage final et renvoie du resultat
        $this->rendered = true;
//    return $result.$htmlNavigation;
        return $result;


//Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        //Gestion de 'laffichage ou non
        if ($this->rendered)
            return '';
        //on met a jour par rapport au portefeuille
        $this->setDroitsParPortefeuilles();

        if ($this->edit && $this->enabled) {
            $this->addResultClass();
            $result = sprintf('<input type="%s" name="%s" value="%s" %s />', $this->attributes['type'], sprintf($this->nameFormat, $this->attributes['name']), $this->attributes['value'], $this->generateAttributes($extraAttributes, $replace));
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                $result = sprintf('<span %s>%s</span>', $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']);
            } else {
                $result = sprintf('<span %s>&nbsp;</span>', $this->generateAttributes($extraAttributes, $replace));
            }
        }
        //On marque le champ comme rendu pour indiquer que le widget a ete rendu et eviter le rendu multiple
        $this->rendered = true;
        return $result . $this->renderHelp() . $this->renderAdminMenu();
    }

    protected function parseOptions() {
        if (isset($this->options['lines']) && $this->options['lines'] != 0) {
            $this->nbElement = $this->options['lines'];
        } else {
            $this->nbElement = 10;
        }

        if (isset($this->options['largeur'])) {
            $largeur = $this->options['largeur'];
            if (is_string($largeur)) {
                $largeur = explode(',', $this->options['largeur']);
            }
            $this->largeurs = $largeur;
        }

        if (isset($this->options['tri'])) {
            $this->order = $this->options['tri'];
        }

        //champs de la valeur de retour, si non specifié retourne la cle
        if (isset($this->options['retour'])) {
            $this->retour = $this->options['retour'];
        } else {
//      $this->retour = 'id';
            $this->retour = false;
        }
        $this->recupsColonnes();
    }

    protected function recupsColonnes() {
        $listeColonnes = array();

        //on transforme tout en un beau tableau
        if (isset($this->options['cols'])) {
            $listeOption = $this->options['cols'];
            if (is_string($listeOption)) {
                if ($listeOption != '') {
                    //On parse la chaine au format nom_col1,nom_col2:libelle col 2
                    $tempCols = explode(",", $listeOption);
                    $listeOptTemp = array();
                    foreach ($tempCols as $col) {
                        $decoupe = explode(':', $col, 2);
                        if (count($decoupe) == 1) {
                            //uniquement le nom de la colonne
                            $listeOptTemp[] = $decoupe[0];
                        } else {
                            //nom de colonne et libellé
                            $listeOptTemp[$decoupe[0]] = $decoupe[1];
                        }
                    }
                    $listeOption = $listeOptTemp;
                } else {
                    $listeOption = array();
                }
            }
        } else {
            $listeOption = array();
        }

        //On traite les options
        if (count($listeOption) > 0) {
//      $listeOption = $this->options['cols'];
            $listeTemp = array();
            foreach ($listeOption as $nomCol => $libelleCol) {
                if (is_numeric($nomCol)) { //l'indice est un nombre, on considere qu'on ne lui a pas associé de clé. Dans ce cas le label correspond au nom de la colonne
                    $listeColonnes[$libelleCol] = str_replace('_', ' ', ucfirst($libelleCol));
                } else { //dans ce cas la on a fournis  'nom colonne'=>'libelle colonne'
                    $listeColonnes[$nomCol] = $libelleCol;
                }
            }
        } else {
//      $colonneTable = $this->colonneTable; //Doctrine_Core::getTable($this->nomTable)->getColumnNames();
            foreach ($this->colonnesTable as $colonne) {
                $listeColonnes[$colonne] = str_replace('_', ' ', ucfirst($colonne));
            }
        }
        $this->listColonnes = $listeColonnes;
    }

    protected function generateCollection() {
        $select = '';
        //on commence par mettre les colonnes index de la table
        $colonneIndex = mmSQL::getCleUnique($this->nomTable);
        foreach ($colonneIndex as $nomCol) {
            $select .= ",$nomCol";
        }
        $select = substr($select, 1);
        foreach ($this->listColonnes as $colonne => $libelle) {
            $select .= ',' . mmParseSqlConcat($colonne, $this->colonnesTable);
//      //On s'en fout du libelle ici
//      //on analyse pour savoir si on doit concatener
//      $nomColonne = str_replace(array('+', ' '), '_', $colonne);
//      if (strpos($colonne, '+') !== false)
//      {
//        $tableauParametre = explode('+', $colonne);
//        $concat = '';
//        foreach ($tableauParametre as $fragment)
//        {
//          if (in_array($fragment, $this->colonnesTable))
//          {
//            $concat .= ",$fragment";
//          }
//          else
//          {
//            $concat .= ",'$fragment'";
//          }
//        }
//        //on vire la premiere ','
//        $concat = substr($concat, 1);
//        //on ajoute au select
//        $select .= ",CONCAT($concat) AS $nomColonne";
//      }
//      else
//      {
//        $select .= ",$colonne AS $nomColonne";
//      }
        }
//    $select = substr($select, 1);
        //
    $this->collection = Doctrine_Query::create()->
                select($select)->
                from($this->nomTable)->
                where($this->sqlWhere)->
                limit($this->nbElement)->
                offset($this->nbElement * ($this->pageCourante - 1));
//A metre au point apres coup
        if ($this->order) {
            $this->collection = $this->collection->orderBy($this->order);
        }
        $this->collection = $this->collection->execute();
    }

}