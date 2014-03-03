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
@file : pInputPopup.php
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


class pInputPopup extends mmProgProceduralWebService{
  public function principale($action = '', $getpost = null) {
    $this->context = new mmContext('__mdInputPopup__'); //nom unique, on a qu'un popup ouvert a la fois
    
    $resultat = $this->initForm($getpost);
    if ($resultat == false)
    {
      echo mmErrorMessage('Aucun parametre fournis');
      return false;
    }
    //echo $this->form;
    $this->genereHtml();
  }
  
  protected function genereHtml()
  {
  ?>
<fieldset>
  <legend>Recherche</legend>
  <?php echo $this->form->renderFormHeader() ?>
  Votre choix : <?php echo $this->form['critere'].$this->form['chercher'] ?><br />
  <?php $htmlListe = $this->form['listeResultat']->render();
          echo $htmlListe;
          ?>
  </form>
</fieldset>
<?php
  $script = $this->form->renderJavascript(null, true);
  echo $script;
  }
  
  protected function initForm($getpost)
  {
    //Aller ou retour ?
    $retour = count($_POST);
    // recuperation des options
    $o=$getpost->getParam('o', false);
    if ($o == false) //On a pas fournis d'option ? On va chercher dans le context
    {
      if ($retour)
      {
        $options = $this->context->get('o');
      }
      else
      {
        return false;
      }
    }
    else
    {
      $optionsDefaut = array(
          'vi'        =>'', //valeur initiale
          'cols'      =>'', //liste des colonnes
          'libelle'   =>'', //libellé des colonnes
          'table'     =>'', //table de la base
          'cle'       =>'', //clé de recherche
          'tri'       =>'', //option de tri
          'retour'    =>'', //champ utilisé pour le retour lors d'un clic
          'widget'    =>''  //nom du parent appelant
      );
      $options = mmParseOptions($o, $optionsDefaut);
      //initialisation des parametres
      $this->context->set('o', $options);
    }
    
    //creation et parametrage du formulaire
    $form = new mmForm();
    $form->setAction('?module=pInputPopup');
    $form->addWidget(new mmWidgetText('critere', $options['vi'], array('size'=>15)));
    $form->addWidget(new mmWidgetButtonSubmit('Chercher'));
    $form->addWidget(new mmWidgetButtonClose());
    //Generation de la liste ou de rien en fonction de l'etat de retour
    if ($retour == 0) //c'est un aller
    {
      $form->addWidget(new mmWidgetBlank('listeResultat'));
    }
    else
    {
      //Preparation des parametres du widget
      
      //recuperation de la valeur rechercher
      $critere = $getpost->getParam('critere', '');
      //clause de recherche
      $listeCle = explode(',', $options['cle']);
      $sqlWhere = '';
      foreach ($listeCle as $cle)
      {
        $sqlWhere .= "OR $cle like '$critere%'";
      }
      $sqlWhere = substr($sqlWhere, 3);
      //action si on click
      $click = "$('#{$options['widget']}').val('%s');$('#__mdDialog').jqmHide()";
      //le champ de retour, celui specifier dans les options ou le premier champ de la cle de recherche le cas echeant
      if ($options['retour'] !== '')
      {
        $retour = $options['retour'];
      }
      else
      {
        $retour = $listeCle[0];
      }
      //libelle des colonnes a afficher
      $colsEtEntete = array();
      $i = 0;
      $listeCol = explode(',',$options['cols']);
      $listeLib = explode(',',$options['libelle']);
      foreach($listeCol as $colonne)
      {
        if (isset($listeLib[$i]))
        {
          $libelle = $listeLib[$i];
        }
        else
        {
          $libelle = $colonne;
        }
        
        $colsEtEntete[$colonne] = $libelle;
        $i++;
      }
      $optionsListe = array(
          'cols'=>$colsEtEntete,
          'tri'=>$options['tri'],
          'retour'=>$retour
      );
      //Largeurs
      if (isset($options['largeur']))
      {
        $optionsListe['largeur'] = $options['largeur'];
      }
      //Ajout du widget
      $form->addWidget(new mmWidgetRecordList('listeResultat', $options['table'], $click, $sqlWhere, $optionsListe));
    }
    $script = "
  $('#".$form->getId()."').keydown(function(event){
    if (event.which == 13)
    {
      //on redirige le return
      event.stopPropagation();
      event.preventDefault();
      $('#chercher_id').click();
    }
  });
";
    $form->addJavascript('entrer', $script);
    $this->form = $form;
    return true;
  }
}

?>
