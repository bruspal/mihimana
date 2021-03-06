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
  @file : mmParserFunction.php
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
 * Parse un bloc de ligne au format |l+....|  |l-|
 * TODO : documenter cette fonction
 * @param type $source : Bloc [l+]...[l-]
 * @param type $tableauVariables : tableau des variables accessibles
 * @return type 
 */
function mmParseListe($source, $tableauVariables) {
    $chaineTravail = $source;
    $chaineResultat = '';
    //On cherche le bloc
    $posDebut = strpos($chaineTravail, '[l+');
    while ($posDebut !== false) {
        $posFin = strpos($source, '[l-]', $posDebut);
        if ($posFin === false) {
            //On ouvre mais on ferme pas: erreur renvoyé a l'utilisateur
            return "$chaineResultat<strong>$chaineTravail</strong><span class=\"mdError\">Pas de balise |l-| trouvée</span>";
        }
        //On commence par recupérer la chaine codant la liste
        $chaineListe = substr($chaineTravail, $posDebut, $posFin - $posDebut);
        //On vire toute trace de html
        $chaineListe = strip_tags($chaineListe);
        //On prepare la suite pour le traitement de la liste suivante au cas ou. Et preparation des chaines au cas ou ou souhaire remonter des erreurs et etc
        $chaineResultat .= substr($chaineTravail, 0, $posDebut);
        $chaineTravail = subStr($chaineTravail, $posFin + 4);

        //On met dans un tableau chaque ligne du bloc de parametrage
        $tableauTravail = explode("\n", $chaineListe);

        //Debut de l'analyse des parametres de la ligne 1
        //Netoyage de la chaine des parametres
        $chaineParametre = substr($tableauTravail[0], 3);
        $chaineParametre = str_replace(array('[', ']'), '', $chaineParametre);

        $options = mmParseOptions($chaineParametre);
        if (!isset($options['lines'])) {
            $options['lines'] = count($tableauTravail) - 1;
        }
        //On analyse le resultat et on parametre
        //parametres obligatoires
        if (!isset($options['table'])) {
            //Erreur ici dans le formatage general du bloc on a pas toutes les lignes ou trop de lignes
            return "$chaineResultat<strong>$chaineListe</strong><span class=\"mdError\">le parametre table='nom de la table' est manquant</span>$chaineTravail";
        }
        if (!isset($options['click'])) {
            //Erreur ici dans le formatage general du bloc on a pas toutes les lignes ou trop de lignes
            return "$chaineResultat<strong>$chaineListe</strong><span class=\"mdError\">le parametre click='action' est manquant</span>$chaineTravail";
        }
        //le nom
        if (!isset($options['nom'])) {
            return "$chaineResultat<strong>$chaineListe</strong><span class=\"mdError\">le parametre nom='nom system' est manquant</span>$chaineTravail";
        }

        //On travail sur la requette pour remplacer la variable par sa valeur
        if (isset($options['where'])) {
            $where = mmParseVariablesValue($options['where'], $tableauVariables);
        } else {
            $where = '1=1';
        }
        //Recuperation des collonnes a afficher et de leur libellé
        $champAffiche = trim(preg_replace('#\s+#', ' ', $tableauTravail[2]));
        $libelle = trim(preg_replace('#\s+#', ' ', $tableauTravail[1]));

        $tableauChampAffiche = explode(';', $champAffiche);
        $tableauLibelle = explode(';', $libelle);

        if (count($tableauChampAffiche) != count($tableauLibelle)) {
            //pas le meme nombre de colonne et de libelle
            return "$chaineResultat<strong>$chaineListe</strong><span class=\"mdError\">Nombre de colonne et de libellé différent</span>$chaineTravail";
        }

        $tableauColonne = array();
        for ($i = 0; $i < count($tableauLibelle); $i++) {
            $tableauColonne[$tableauChampAffiche[$i]] = $tableauLibelle[$i];
        }
        $options['cols'] = $tableauColonne;
        //Prise en compte des largeurs
        $largeurs = trim(preg_replace('#\s+#', ' ', $tableauTravail[3]));
        $options['largeurs'] = $largeurs;
        //Generation et recuperation du html de la liste
        $liste = new mmWidgetRecordList($options['nom'], $options['table'], $options['click'], $where, $options);

        //On a tout creer, on recupere maintenant le html du tableau qui remplace le parametrage
        $htmlListe = $liste->render();

        //On ajoute ce html au resultat
        $chaineResultat .= $htmlListe;
        //On verifie si il y'a une autre liste dans la page
        $posDebut = strpos($chaineTravail, '[l+');
    }

    return $chaineResultat . $chaineTravail;
}

/**
 * Analyse $chaineOptions et retourne un tableau de la forme array($option1=>valeurs1, $option2=>valeur2,...)
 * @param type $chaineOptions
 * @return type 
 */
function mmParseOptions($chaineOptions, $optionDefaut = array()) {
    //Si on a pas de parametre
    if (trim($chaineOptions) == '') {
        return $optionDefaut;
    }

    //Premiere chose on transforme l'entrée pour qu'elle soit compatible
    $options = preg_replace('#([^\\\\]);#', "$1\n", $chaineOptions);
    $options = str_replace('\\;', ";", $options);
    //On traite ligne par ligne
    $result = $optionDefaut;
    $tableauChaines = explode("\n", $options);
    foreach ($tableauChaines as $chaineCourante) {
        $chaineCourante = trim($chaineCourante);
        if ($chaineCourante != '') {
            $tableauTemp = explode('=', $chaineCourante, 2);
            if ($tableauTemp[0] == $chaineCourante) {
                //C'est un parametre anonnyme. On lui attribut le nom 'default' par defaut. On emet un warning si une valeur par defaut existe deja
                //mais on ecrase tout de meme
                if (isset($result['default'])) {
                    mmUser::flashWarning("Un parametre anonyme a été écrasé");
                }
                $result['default'] = $tableauTemp[0];
            } else {
                //options nommée standard
                list($nomOption, $valeur) = $tableauTemp;
                $result[$nomOption] = $valeur;
            }
        }
    }
    return $result;
}

function mmParseVariablesValue($chaine, $tableauVariables = array()) {
    $GLOBALS['mdParseValeurVariables'] = $tableauVariables;
    //Pour chaque morceau de chaine commencant par $ et suivie de caractere, chiffres ou _ appel de la function __mdParseValeurVariablesCallback
    $result = preg_replace_callback('#([\\\\]?)\$(\w+)((\["?[\w]*"?\])*)#', '__mmParseValeurVariablesCallback', $chaine);

    //on nettoie les variables globals avant de poursuivre
    unset($GLOBALS['mdParseValeurVariables']);
    return $result;
}

function mmParseSqlConcat($colonne, $listeColonnesTable, $seulementAlias = false) {
    $nomColonne = str_replace(array('+', ' '), '_', $colonne);
    if ($seulementAlias) {
        return $nomColonne;
    } else {
        if (strpos($colonne, '+') !== false) {
            //Si on fournis une chaine on va chercher la liste des champs de la table
            if (is_string($listeColonnesTable)) {
                $colonneTable = Doctrine_Core::getTable($listeColonnesTable)->getColumnNames();
            }
            //sinon on considere que c'est un tableau contenant la liste des champs
            //TODO: ajouter des controle
            else {
                $colonneTable = $listeColonnesTable;
            }
            $tableauParametre = explode('+', $colonne);
            $concat = '';
            foreach ($tableauParametre as $fragment) {
                if (in_array($fragment, $colonneTable)) {
                    $concat .= ",$fragment";
                } else {
                    $concat .= ",'$fragment'";
                }
            }
            //on vire la premiere ','
            $concat = substr($concat, 1);
            //on ajoute au select
            $result = "CONCAT($concat) AS $nomColonne";
        } else {
            $result = "$colonne AS $nomColonne";
        }
        return $result;
    }
}

/*
 * Ensemble des function de callback de callback
 */

function __mmParseValeurVariablesCallback($fragments) {
    //si la variables est échappée on retourne juste la chaine sans le \
    if ($fragments[1] == '\\') {
        return substr($fragments[0], 1);
    }
    $data = $GLOBALS['mdParseValeurVariables'];
    //La variable existe ?
    $nomVariable = $fragments[2];
    if (isset($data[$nomVariable])) {
        $valeur = $data[$nomVariable];
        if (!is_scalar($valeur)) { // si ce n'est pas un type simple, on traite les indices
            $indice = explode(']', $fragments[3]); //des indice on fais un tableau de nom d'indices
            array_pop($indice); //pour virer les dernier element du tableau on le pop
            foreach ($indice as $i) {
                $i = substr($i, 1);
                if (is_numeric($i)) {
                    $i = (int) $i;
                } else {
                    $i = str_replace('"', '', $i);
                }
                if (isset($valeur[$i])) {
                    $valeur = $valeur[$i];
                } else {
                    mmUser::flashError($fragments[0] . ": indice $i inconnu");
                    return $fragments[0];
                }
            }
        }

        return $valeur;
    } else {
        // variable inconnue on genere une exception
        throw new mmExceptionControl("La variables <strong>{$fragments[2]}</strong> n'existe pas");
    }
}