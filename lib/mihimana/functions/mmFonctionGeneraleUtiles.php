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
  @file : mmFonctionGeneraleUtiles.php
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
 * fonction standard utilisé partout
 */

function estRetour() {
    if (count($_POST) > 0) {
        return true;
    } else {
        return false;
    }
}

function versApache($chaine) {
    echo $chaine;
}

function affectationEtControleAuto(mmScreen & $ecran, $getPost) {
    $saisie = $getPost->get($ecran->getName(), false);
    if ($ecran->setValues($saisie) == true) {
        return true;
    } else {
        return false;
    }
}

function reafficheEcranMisAJour($ecran) {
    $enregistrement = $ecran->getRecord();
    if ($enregistrement) {
        //si on a un enregistrement associé a l'écran on va rechercher cet ecran par rapport a la clé primaire
        redirect(sprintf('?module=' . MODULE_COURANT . '&action=' . ACTION_COURANTE . '&b=%s', mmSQL::genereChaineIndex($enregistrement)));
//<------ On sort ici 
    } else {
        //sinon on rappel simplement la page
        redirect('?module=' . MODULE_COURANT . '&action=' . ACTION_COURANTE);
//<------ On sort ici 
    }
}

function recupererValeurGetPostOuSession($saisieGetPost, & $session, $nomVar, $defaut = -1) {
    $resultat = $saisieGetPost->get($nomVar, false);

    if ($resultat === false) {
        //on va chercher dans la session
        $resultat = $session->get($nomVar, false);
        if ($resultat === false) {
            //erreur on a pas de clé, par defaut on est en création
            mmUser::flashWarning("Pas de clé, mode création par defaut");
            $resultat = $defaut;
            $session->set($nomVar, $defaut);
        }
    } else {
        $session->set($nomVar, $resultat);
    }
    return $resultat;
}
