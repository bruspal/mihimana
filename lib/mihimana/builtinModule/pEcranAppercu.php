<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: builtinModule
@file : pEcranAppercu.php
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



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Ce programme genere l'a&ppercu d'un ecran
 *
 * @author bruno
 */
class pEcranAppercu extends mmProgCRUD
{
  /**
   * 
   */
  public function configure()
  {
    //recuperation des parametres qui vont bien.
    $nomEcran = $this->parametresProgramme->getParam('eran', false);
    if ( ! $nomEcran)
    {
      $nomEcran = mmSession::get('__editionNomEcran__', false);
      if ( ! $nomEcran)
      {
        throw new mmExceptionControl("Impossible d'afficher l'appercu. Aucun nom d'ecran fournis");
      }
    }
    else
    {
      mmSession::set('__editionNomEcran__', $nomEcran);
    }
    //On recupere les infos de l'ecran
    $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
    if ( ! $ecran)
    {
      throw new mmExceptionControl("L'écran $nomEcran n'est pas défini");
    }
    $this->screenName = $nomEcran;
    $this->tableName = $ecran['table_liee'];
    $this->setLayout('popup');
    $this->options['genereIndex'] = false;
  }
  
  public function initForm(Doctrine_Record $table, $nouveau) {
    parent::initForm($table, $nouveau);
    if ( ! $this->form instanceof mmScreen)
    {
      unset($this->form['enregistrer']);
      unset($this->form['precedent']);
    }
    $this->form->setAppercuOn();
    $this->form->addWidget(new mmWidgetButtonClose());
  }
  
  public function executeIndex(mmRequest $request) {
    $this->executeNouveau($request);
  }
  
  public function executeEdit(mmRequest $request) {
    $this->executeNouveau($request);
  }
  
  public function enregistrer(mmRequest $request) {
    //Methode vide pour annuler le comportement de la classe parent()
    $this->executeNouveau($request);
  }
  
  public function afficheFormulaire() {
    if ($this->form->getDestination() == 'scr')
    {
      parent::afficheFormulaire();
      mmUser::clearFlashes();
    }
    else
    {
      $this->setLayout();
      $this->setTemplate();
      
      $pdf = new mmPdf();
      $srcPdf = $pdf->generateFromScreen($this->form);
      if ($srcPdf === false)
      {
        echo $pdf->getError();
      }
      else
      {
        mmOutputPdf($srcPdf);
      }
    }
  }
}

?>
