<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: fichierTypes
@file : programmeType.php
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
------------------------------------------------------------------------------*/


/*********
 * ATTENTION REMPLACER 'programmeType' PAR STRICTEMENT LE MEME NOM QUE LE FICHIER SOURCE MAIS SANS EXTENSION
 */
class programmeType extends mmProgProcedural
{
/**************************************************************************************
 * Boucle principale --------------------------------------------------------------------------------------------------------------
 **************************************************************************************/
  public function principale($action = '', $saisieGetPost = null)
  {
    /****
     * Initialisation:
     ****/
    //nom de l'ecran associé au programme
    $this->nomEcran = 'toto';
    //nom de la table de données associé au programme (ATTENTION : Ce nom doit correspondre a celui spécifié comme 'table_lié' dans l'ecran
    //si il n'y a pas de table lié la variable doit etre mise a false comme ci dessous
    // $this->nomTable = false;
    $this->nomTable = 'ficreg';
    
    /****
     * Debut du traitement
     ****/
    
    $this->enregistrement = $this->initialiseDonnees($this->nomTable, $saisieGetPost);
    
    $ecran = $this->initialiseEcran($saisieGetPost);
    if(estRetour())
    {
      //C'est un retour
      //On fait l'affectation et les controle auto. si il y'a au moins une erreur $controleAuto vaudra faux sinon vrai
      $controlAuto = affectationEtControleAuto($ecran, $saisieGetPost);
      $controlPerso = $this->controlsSpecifique($ecran, $saisieGetPost);
      if ($controlAuto == true && $controlPerso == true)
      {
        $ecran->save();
        reafficheEcranMisAJour($ecran);
      }
      else
      {
        mmUser::flashError("Saisie incorrecte");
        $html = $this->genereHtml($ecran);
        versApache($html);
      }
    }
    else
    {
      //c'est l'aller
      $html = $this->genereHtml($ecran);
      versApache($html);
    }
  }
/**************************************************************************************
 * Fin boucle principale --------------------------------------------------------------------------------------------------------------
 **************************************************************************************/
  
  
  
  
  
  
  
  
/**************************************************************************************
 * Traitements spécifique --------------------------------------------------------------------------------------------------------------
 **************************************************************************************/
  
  protected function initialiseDonnees($nomFichier, $saisieGetPost)
  {
    if ($nomFichier == false) //condition large: est false les valeur 0, null, ''
    {
      return null;
    }
    
    $this->session = new mmContext(MODULE_COURANT);
    $cle = recupererValeurGetPostOuSession($saisieGetPost, $this->session, 'b', -1);
//    $cle = $saisieGetPost->get('b', false);
//    
//    if ($cle === false)
//    {
//      //on va chercher dans la session
//      $cle = $this->session->get('b', false);
//      if ($cle === false)
//      {
//        //erreur on a pas de clé, par defaut on est en création
//        mdUser::flashWarning ("Pas de clé, mode création par defaut");
//        $cle = -1;
//        $this->session->set('b', -1);
//      }
//    }
//    else
//    {
//      $this->session->set('b', $cle);
//    }
//    
//    if ($cle !== false)
//    {
      if ($cle == '-1')
      {
        $enreg = new $nomFichier();
      }
      else
      {
        $tableauCle = mmSQL::genereIndex($cle);
        $enreg = Doctrine_Core::getTable($nomFichier)->find($tableauCle);
      }
//    }
    
    return $enreg;
  }
  
  protected function initialiseEcran($saisieGetPost)
  {
    $ecran = new mmScreen($this->nomEcran, $this->enregistrement);
    return $ecran;
  }
  
  protected function controlsSpecifique($ecran, $saisieGetPost)
  {
    return true;
  }
  
  protected function genereHtml($ecran)
  {
    return $ecran->render();
  }
}

/**************************************************************************************
 * Traitements spécifique --------------------------------------------------------------------------------------------------------------
 **************************************************************************************/


?>
