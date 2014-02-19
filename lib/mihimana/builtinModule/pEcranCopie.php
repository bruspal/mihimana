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
@file : pEcranCopie.php
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



class pEcranCopie extends mmProgProcedural
{
  public function principale($action = '', mmRequest $parametres = null) {
    if ( ! $this->initForm($parametres))
    {
      return false;
    }
    if (count($_POST) == 0)
    {
      //c'est un allé
      $this->genereHtml();
    }
    else
    {
      //c'est un retour
      $saisie = $parametres->toArray();
      $nvNom = $parametres->getParam('n', '');
      if ( $nvNom == false)
      {
        echo "Le nouveau nom ne doit pas etre vide";
        goto suite;
      }
      $acNom = $parametres->getParam('a', false);
      if ( $acNom == false)
      {
        echo "Le nom initial ne doit pas etre vide";
        goto suite;
      }
      $this->copie($acNom, $nvNom);
      echo "Tout est OK";
      suite:
      $this->genereHtml();
    }
  }
  
  protected function genereHtml()
  {
    echo $this->form;  
  }
  protected function copie($acNom, $nvNom){
    $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($acNom);
//    $nvTable = new TableUtilisateur();
    $nvEcran = $ecran->copy();
    $nvEcran['nom_ecran'] = $nvNom;
    $nvEcran->save();
    $listeChamp = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->where('nom_ecran = ?', $acNom)->execute();
    foreach ($listeChamp as $champ)
    {
      $nvChamp = $champ->copy();
      $nvChamp['nom_ecran'] = $nvNom;
      $nvChamp->save();
    }
  }
  
  public function initForm($parametres)
  {
    $nvNom = $parametres->getParam('n', '');
    $acNom = $parametres->getParam('a', false);
    if ( $acNom == false)
    {
      echo "Le nom initial ne doit pas etre vide";
      return false;
    }
    $form = new mmForm();
    $form->setAction('?module=pEcranCopie&a='.$acNom);
    $a = new mmWidgetHidden('a', $acNom);
    
    $nv = new mmWidgetText('n', $nvNom);
    $nv->setLabel('Nouveau nom');
    $form->addWidget($nv);
    $form->addWidget(new mmWidgetButtonSubmit());
    $form->addWidget(new mmWidgetButtonClose());
    $this->form = $form;
    
    return true;
  }
}

?>
