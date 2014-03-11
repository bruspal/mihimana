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
@file : pEcranAssign.php
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
require_once 'widgetsList.php';
/**
 * Description of pEcranAssign
 *
 * @author bruno
 */
class pEcranAssign extends mmProgProcedural {

    //'globale' au programme
            protected
            $typeChamp = array(
                'var' => 'Variable de la table associée',
                'lbr' => 'Champ libre',
                'anc' => 'Ancrage'
    );

    public function main($action, mmRequest $request) {
        $this->verifDroits();
        switch ($action) {
            case 'index':
                $this->ajout($request);
                break;
            default:
                throw new mmException('Appel incorrecte');
                break;
        }
    }

    public function verifDroits() {
        if (!mmUser::superAdmin()) {
            mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
            $this->redirect('?');
        }
    }

    protected function ajout($parametres) {
        $nomEcran = mmUser::get('__editionNomEcran__');

        //on recupere les infos ou on en cré de nouvelle
        $operation = $parametres->get('o', 'i');
        if ($operation == 'e') {
            //On est dans le cas d'une edition
            $nomChamp = $parametres->get('champ', false);
            if ($nomChamp) {
                $champsEcran = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->where('nom_ecran = ? AND nom_champ = ?', array($nomEcran, $nomChamp))->fetchOne();
                if (!$champsEcran) {
                    $champsEcran = new ChampsEcranUtilisateur();
                    $champsEcran['type_champ'] = 'anc';
                    $champsEcran['nom_ecran'] = $nomEcran;
                    $champsEcran['nom_champ'] = $nomChamp;
                }
            } else {
                throw new mmExceptionControl('Tentative d\'edition d\'un champ sans fournir de nom de champ');
            }
        } else {
            //On est dans le cas d'une insertion
            $champsEcran = new ChampsEcranUtilisateur();
            $champsEcran['nom_ecran'] = $nomEcran;
            //On recupere les information de la table liée
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
            if (!$ecran) {
                mmErrorMessage("L'écran associé est introuvable");
                return false;
            }
            $nomTable = $ecran['table_liee'];
            if (trim($nomTable) == '') {
                //C'est une table vide. On a pas le choix c'est un champ libre
                $champsEcran['type_champ'] = 'lbr';
            }

//        $champsEcran['numero_ordre'] = $this->numVariable;
            //On initialise par defaut les valeurs du champs avec les valeurs par defaut dans la description de la base
//           /\
//          /  \
//         / !! \  Debile ici on manque d'info pertinente
//        /______\
//        $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
//        if ($ecran)
//        {
//          $nomTable = $ecran['table_liee'];
//        }
//        else
//        {
//          $nomTable = false;
//        }
//        //on met a jour la liste des champs preparametré
//        if ($nomTable != false)
//        {
//          $infoDepuisDescriptionTable = Doctrine_Core::getTable('TableUtilisateur')->createQuery()->where('table_liee = ?', $nomTable);
//          foreach
//          $listeNomChampACopier = array('type_widget', 'option_type_widget', 'libelle');
//        }
        }

        if (count($_POST) == 0) {
            //On creer un formulaire standard
            $this->form = $this->prepareFormulaire($champsEcran, $operation);
            //premier appel (c'est a dire on affiche les champs pour la première fois)
            $this->afficheFormulaire();
        } else {
            $donneesSaisie = $parametres->get('champs_ecran_utilisateur');

            //traitement en fonction des type de champs
            switch ($donneesSaisie['type_champ']) {
                case 'lbr':
                case 'anc':
                    //saisie de champs libre ou ancrage
                    $donneesSaisie['nom_champ'] = $donneesSaisie['nom_champ_txt'];
                    break;

                default:
                    //par defaut on change rien
                    break;
            }
            //On verifie si dans le cas d'une insertion un champ existe deja dans la base
            if ($operation == 'i') {
                $champTemp = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->
                        where("nom_ecran = ? AND nom_champ = ?", array($champsEcran['nom_ecran'], $donneesSaisie['nom_champ']))->
                        fetchOne();
//        $text = get_raw_sql($champTemp);
//                
//                fetchOne();
                if ($champTemp != false) {
                    $champsEcran = $champTemp;
                }
            }
            $this->form = $this->prepareFormulaire($champsEcran, $operation);

            //avant d'enregistrer et si le champ de la table est nouveau et est un champ libre on verifie que le nom du champ n'existe pas dans la base
            $valide = true;
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran); //$champsEcran->get('EcranUtilisateur');
            if ($this->form->isNew() && $donneesSaisie['type_champ'] == 'lbr') {
                //le champ existe deja dans la base ?
                $champExiste = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->where('nom_ecran = ? AND nom_champ = ?', array($nomEcran, $donneesSaisie['nom_champ']))->fetchOne();
                if ($champExiste) {
                    $champExiste->delete();
                }

                //si le champ n'existe pas dans le champs de l'ecran, on cherche dans la table lié si elle existe
                if (!$ecran) {
                    mmErrorMessage("Impossible de trouver l'écran associé");
                    return false;
//<------ Sortie du programme en affichant un message d'erreur          
                }
                $nomTable = $ecran['table_liee'];

                if (trim($nomTable != '')) {
                    if ($valide) {
                        $champExiste = Doctrine_Core::getTable('ChampsTableUtilisateur')->createQuery()->where('nom_table = ? AND nom_champ = ?', array($nomTable, $donneesSaisie['nom_champ']))->fetchOne();
                        if ($champExiste) {
                            try {
                                $this->form['nom_champ']->addError('Le nom du champ existe deja dans la table utilisateur');
                            } catch (mmExceptionWidget $e) {
                                $valide = false;
                            }
                        }
                    }
                }
            }
            //Validation automatique et assignation
            if ($this->form->setValues($donneesSaisie) && $valide) {
                //on enregistre que pour les champs libre ou les champs de la table
                if ($donneesSaisie['type_champ'] == 'var' || $donneesSaisie['type_champ'] == 'lbr') {
                    $champValide = $this->form->save();
                }

                if ($ecran['mode_rendu'] == 'txt') {
                    //comportement en mode lima
                    if ($operation == 'e') {
                        echo '<script type="text/javascript">
              nettoieChaineVariable(cPosition);
              cPosition--;
              insertChaine("$' . $donneesSaisie['nom_champ'] . '");
              $("#__mmDialog").jqmHide();
              </script>';
                    } else {
                        echo '<script type="text/javascript">
              insertChaine("' . $donneesSaisie['nom_champ'] . ' ");
              $("#__mmDialog").jqmHide();
              </script>';
                    }
                } else {
                    //comportement en mode CKEditor
                    if ($operation == 'e') {
                        echo '<script type="text/javascript">$("#__mmDialog").jqmHide();</script>';
                    } else {
                        printf('<script type="text/javascript">CKEDITOR.instances.ecran_utilisateur_template_id.insertText("$%s");$("#__mmDialog").jqmHide();</script>', $donneesSaisie['nom_champ']);
                    }
                }
                return true;
//<----- sortie          
            } else {
                mmErrorMessage('Il y\'a des erreurs de saisie');
                $this->afficheFormulaire();
            }
        }
    }

//  protected function supprime($parametres)
//  {
//    $nomEcran = User::get('__editionNomEcran__');
//    $this->numVariable = $parametres->get('n', false);
//    if (! $this->numVariable)
//    {
//      echo mdErrorMessage('Aucun numero de variable fournis.'.new mdWidgetButtonClose());
//    }
//    else
//    {
//      //On commence par rechercher la variable
//      $enregistrement = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->
//              where('nom_ecran = ? AND numero_ordre = ?', array($nomEcran, $this->numVariable))->
//              fetchOne();
//      if ( ! $enregistrement)
//      {
//        echo mdErrorMessage('aucune variable associé a ce $');
//      }
//      else
//      {
//        $enregistrement->delete();
//        //on effectue le decalage
//        $requete = Doctrine_Query::create()->
//                update('ChampsEcranUtilisateur')->
//                set('numero_ordre', 'numero_ordre - 1')->
//                where('nom_ecran = ? AND numero_ordre > ?', array($nomEcran, $this->numVariable))->
//                execute();
//        //on termine le programme en demandant a la popup ajax de se fermer
//        echo '<script type="text/javascript">
//          $("#__mmDialog").jqmHide();
//          $("#ecran_utilisateur_id").submit();
//          </script>';
//        return true;
////<----- sortie          
//      }
//    }
//  }

    protected function prepareFormulaire($champsEcran, $operation) {
        //creation du formulaire standard
        $form = new mmForm($champsEcran);
        //personnalisation du formulaire
        $form->setAction(url("pEcranAssign?o=$operation&champ={$champsEcran['nom_champ']}"));
//                sprintf('?module=pEcranAssign&o=%s&champ=%s', $operation, $champsEcran['nom_champ']));

        unset($form['numero_ordre']);
        $form->addWidget(new mmWidgetHidden('op', ''));
        $form->addWidget(new mmWidgetSelect($form['type_champ'], $this->typeChamp));
        $form->addWidget(new mmWidgetSelect($form['type_widget'], $GLOBALS['typeWidget']));
        $form['type_widget']->setInfo('Type de widget pour la saisie');
//        $form->addWidget(new mdWidgetSelect($form['type_var'], $this->typeVar));
//        $form['type_var']->setInfo('Type de variable ecran');
        $form->addwidget(new mmWidgetTextArea($form['option_type_widget'], '', array('cols' => 40, 'rows' => 8, 'style' => 'display: none;')));
        $form->addWidget(new mmWidgetSelect($form['est_lecture_seule'], array(0 => 'Non', 1 => 'Oui')));
        $form['est_lecture_seule']->setInfo('A oui affiche uniquement la valeur');
        $form->addWidget(new mmWidgetSelect($form['calcul_systematique'], array(0 => 'Non', 1 => 'Oui')));
        $form->addWidget(new mmWidgetTextArea($form['formule_calcul'], '', array('cols' => 40, 'rows' => 8)));
        $form['formule_calcul']->setInfo('Formule de calcul appliqué à la valeur du widget');
        $form['info_bulle']->setInfo('Message apparaissant dans la bulle d\'aide');
        $form['libelle']->setInfo('Libellé du widget. libelle du bouton pour les boutons');
        $form->addWidget(new mmWidgetSelect($form['est_notnull'], array(0 => 'Non', 1 => 'Oui')));
        $form['est_notnull']->setInfo('Saisie obligatoire');
        $form['css_attr']->setInfo('Attributs css au format css séparé par ;');
        $form['css_class']->setInfo('classes du widget séparé par un espace');
        $form->addWidget(new mmWidgetTextArea($form['jsclick']));
        $form['jsclick']->setInfo('Javascript éxécuté sur l\'événement click');
        $form->addWidget(new mmWidgetTextArea($form['jsfocus']));
        $form['jsfocus']->setInfo('Javascript éxécuté sur l\'événement focus');
        $form->addWidget(new mmWidgetTextArea($form['jsblur']));
        $form['jsblur']->setInfo('Javascript éxécuté sur l\'événement perte de focus');
        $form->addWidget(new mmWidgetTextArea($form['jschange']));
        $form['jschange']->setInfo('Javascript éxécuté sur l\'événement modification');
        $form->addWidget(new mmWidgetTextArea($form['jsdblclick']));
        $form['jsdblclick']->setInfo('Javascript éxécuté sur l\'événement double double click');
        $form->addWidget(new mmWidgetTextArea($form['jsrclick']));
        $form['jsrclick']->setInfo('Javascript éxécuté sur l\'événement click droit');
        if ($operation == 'i') {
            $form->addWidget(new mmWidgetButtonSubmit('Inserer', "creerOptions();$('#op_id').val('i')", 'valider_champ'));
            $form->addWidget(new mmWidgetButton('annuler', 'Annuler', array('onclick' => 'reinitSaisie()')));
        } else {
            $form->addWidget(new mmWidgetButtonSubmit('Modifier', "creerOptions();$('#op_id').val('')", 'valider_champ'));
            $form->addWidget(new mmWidgetButton('annuler', 'Annuler', array('onclick' => "$('#__mmDialog').jqmHide()")));
        }

        //On gere le nom du champ, c'est une combo si on a une table lié + un champ de saisie pour le champs libre
        $nomEcran = mmUser::get('__editionNomEcran__', false);
        $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
        if ($ecran) {
            if (trim($ecran['table_liee']) != false) {
                $nomTable = $ecran['table_liee'];
            } else {
                $nomTable = false;
            }
        } else {
            $nomTable = false;
        }

//    $nomTable = $ecran['table_liee'];
        if ($nomTable) {
            //Si on a une table lié on va chercher les champs existants
            //On commence par les champs de la table existante
            $table = Doctrine_Core::getTable($nomTable);
            $colonnes = array();
            foreach ($table->getColumnNames() as $colonneCourante) {
                $colonnes[$colonneCourante] = $colonneCourante;
            }
            //Si le champ est dans la description on ecrase dans la liste pour avoir la description en plus
            $table = Doctrine_Core::getTable('ChampsTableUtilisateur')->findByNomTable($nomTable);
            foreach ($table as $colonneCourante) {
                $colonnes[$colonneCourante['nom_champ']] = $colonneCourante['nom_champ'] . ' - ' . $colonneCourante['libelle'];
            }
            //on parametre les champs en consequence. pour un positionnement plus clean on tweak un peu le formulaire en jouant avec le tableau des widget
            $form->addWidget(new mmWidgetList($form['nom_champ'], $colonnes, '', array('size' => '5')));
            $saisieChampText = new mmWidgetText('nom_champ_txt', $champsEcran['nom_champ']);
            $saisieChampText->setLabel('Nom champ');
            $form->addWidget($saisieChampText);

            //en fonction du type de champ on prepare les widgets
            if ($champsEcran['type_champ'] == 'lbr') {
                $form['nom_champ']->addAttribute('style', 'display: none;');
            } else {
                $form['nom_champ_txt']->addAttribute('style', 'display: none;');
            }

            //on ajoute le javascript
            $script = "
$('#type_champ_id').change(function()
{
  majAffichage();
});";
            $form->addJavascript('majAffichage', $script);
            $form->addJavascript('dblClikVar', "$('#".$form['nom_champ']->getId()."').dblclick(function(){ $('#valider_champ_id').click() })");
            $this->afficheChoixSource = true;
        } else {
            $colonnes = array();
//      $form->addWidget(new mdWidgetBlank('nom_champ_txt'));
            $saisieChampText = new mmWidgetText('nom_champ_txt', $champsEcran['nom_champ']);
            $saisieChampText->setLabel('Nom champ');
            $form->addWidget($saisieChampText);
//      $form['type_champ'] = 'lbr';
            $this->afficheChoixSource = false;
        }

        return $form;
    }

    protected function afficheFormulaire() {
        //    echo $this->form; //ancienne methode automatique
        //Ici on met le code en html pour une fenetre maison
        if ($this->form['type_champ']->getValue() == 'lbr') {
            $this->form['nom_champ']->addAttribute('style', 'display: none;');
            $this->form['nom_champ_txt']->addAttribute('style', 'display: block;');
        } else {
            $this->form['nom_champ_txt']->addAttribute('style', 'display: none;');
            $this->form['nom_champ']->addAttribute('style', 'display: block;');
        }
        ?>
        <script type="text/javascript">
            $(function() {
                $( "#onglets" ).tabs({
                    beforeLoad: function( event, ui ) {
                        ui.jqXHR.error(function() {
                            ui.panel.html("Document indisponible");
                        });
                    }
                });
            });

            function majAffichage()
            {
                var type_champ = $('#type_champ_id').val();
                switch(type_champ)
                {
                    case 'var':
                        $('#<?php echo $this->form['nom_champ']->getId() ?>').show();
                        $('#nom_champ_txt_id').hide();
                        $('#t_type_widget_id').show();
                        $('#t_nom_champ_id').show();
                        $('#t_libelle_id').show();
                        $('#t_option_type_widget_id').show();
                        $('#t_est_lecture_seule_id').show();
                        $('#t_formule_calcul').show();
                        $('#t_option_type_champ').hide();
                        $('#t_type_champ').hide();
                        $('#onglets').show();
                        break;
                    case 'val':
                        $('#nom_champ_txt_id').show();
                        $('#<?php echo $this->form['nom_champ']->getId() ?>').hide();
                        $('#t_type_widget_id').show();
                        $('#t_nom_champ_id').hide();
                        $('#t_libelle_id').show();
                        $('#t_option_type_widget_id').show();
                        $('#t_est_lecture_seule_id').show();
                        $('#t_formule_calcul').show();
                        $('#t_option_type_champ').show();
                        $('#t_type_champ').show();
                        $('#onglets').show();
                        break;
                    case 'lbr':
                        $('#nom_champ_txt_id').show();
                        $('#<?php echo $this->form['nom_champ']->getId() ?>').hide();
                        $('#t_type_widget_id').show();
                        $('#t_nom_champ_id').hide();
                        $('#t_libelle_id').show();
                        $('#t_option_type_widget_id').show();
                        $('#t_est_lecture_seule_id').show();
                        $('#t_formule_calcul').show();
                        $('#t_option_type_champ').hide();
                        $('#t_type_champ').hide();
                        $('#onglets').show();
                        break;
                    case 'anc':
                        $('#nom_champ_txt_id').show();
                        $('#<?php echo $this->form['nom_champ']->getId() ?>').hide();
                        $('#t_type_widget_id').hide();
                        $('#t_nom_champ_id').hide();
                        $('#t_libelle_id').hide();
                        $('#t_option_type_widget_id').hide();
                        $('#t_est_lecture_seule_id').hide();
                        $('#t_formule_calcul').hide();
                        $('#t_option_type_champ').hide();
                        $('#t_type_champ').hide();
                        $('#onglets').hide();
                        break;
                }
            }

            function creerTableauOptions(listeOptions, valeurOptions)
            {
                var tableau = $('<table></table>');
                for (i = 0; i < listeOptions.length; i++)
                {
                    var nomOption = listeOptions[i];
                    var valOption = valeurOptions[nomOption];
                    if (valOption == undefined)
                    {
                        valOption = "";
                    }
                    var tr = $('<tr></tr>');
                    var nom = '<th style="width: 7em;">'+nomOption+'</th>';
                    var valeur = '<td><input type="text" class="saisie_option" size="85" nom_option="'+nomOption+'" name="toptions['+nomOption+']" value="'+valOption+'" id="toptions_'+nomOption+'_id" /></td>';
                    tr.append(nom);
                    tr.append(valeur);
                    //    var ligne = '<tr><th>'+nomOption+'</th><td><input type="text" size="20" name="champs_ecran_utilisateur[toptions]['+nomOption+'] value="'+valOption+'" id="toptions_'+nomOption+'_id" /></td><tr>';
                    tableau.append(tr);
                }
                return tableau.html();
            }

            function coupeOptions(chaineOption)
            {
                var resultat = new Array();
                var tableauLigne = chaineOption.split("\n");
                for (i = 0; i < tableauLigne.length; i++)
                {
                    var ligne = tableauLigne[i];
                    var posEgal = ligne.indexOf('=');
                    if (posEgal != -1)
                    {
                        var nomOption = ligne.substring(0, posEgal);
                        var valeurOption = ligne.substring(posEgal+1);
                        resultat[nomOption]=valeurOption;
                    }
                }
                return resultat;
            }

            function creerOptions()
            {
                var resultat = '';
                var collection = $('.saisie_option');
                collection.each(function(i){
                    var nomOption = $(this).attr('nom_option');
                    var valeurOption = $(this).attr('value');
                    if (valeurOption.trim() != '')
                    {
                        resultat = resultat+"\n"+nomOption+"="+valeurOption.trim();
                    }
                });
                resultat = resultat.substring(1);
                $('#<?php echo $this->form['type_widget']->getId() ?>').val(resultat);
            }

            function initialiseOptions()
            {
                var type_widget = $('#<?php echo $this->form['type_widget']->getId() ?>').val();
                var valOptions = coupeOptions($('#<?php echo $this->form['type_widget']->getId() ?>').val());
                var listeOptions = ['largeur'];
                $('#table_options').empty();
                switch (type_widget)
                {
                    case 'button':
                        listeOptions = listeOptions.concat(['click']);
                        break;
                    case 'buttonGoPage':
                    case 'buttonAjaxPopup':
                    case 'buttonHtmlPopup':
                        listeOptions = listeOptions.concat(['url']);
                        break;
                    case 'buttonClose':
                        break;
                    case 'buttonNext':
                    case 'buttonPrec':
                        listeOptions = listeOptions.concat(['cle', 'action']);
                        break;
                    case 'buttonSeqNext':
                    case 'buttonSeqPrec':
                        listeOptions = listeOptions.concat(['sequence', 'action']);
                        break;
                    case 'buttonGoModuleAjaxPopup':
                    case 'buttonGoModuleHtmlPopup':
                        listeOptions = listeOptions.concat(['module', 'action', 'parametres']);
                        break;
                    case 'buttonGoModule':
                        listeOptions = listeOptions.concat(['module', 'action', 'parametres', 'remplace']);
                        break;
                    case 'buttonSubmit':
                        listeOptions = listeOptions.concat(['preSubmit']);
                        break;
                    case 'selectTable':
                        listeOptions = listeOptions.concat(['table']);
                        break;
                    case 'selectFic':
                        listeOptions = listeOptions.concat(['table', 'cle', 'libelle', 'condition']);
                        break;
                    case 'inputPopup':
                        listeOptions = listeOptions.concat(['table', 'cle', 'libelle', 'retour', 'cols']);
                        break;
                    case 'imageSQL':
                        listeOptions = ['max'];
                        break;
                    case 'recordList':
                        listeOptions = listeOptions.concat(['table', 'action', 'condition', 'lines', 'retour', 'cols']);
                        break;
                    case 'recordCle':
                        listeOptions = listeOptions.concat(['table', 'cle', 'actionListe', 'actionNouveau', 'cols', 'auto']);
                        break;
                    case 'textArea':
                    case 'blob':
                    case 'clob':
                        listeOptions = ['cols', 'lines'];
                        break;
                    case 'CKEditor':
                    case 'TinyMce':
                    case 'hidden':
                        listeOptions = [];
                        break;
                    case 'boolean':
                    case 'date':
                    case 'decimal':
                    case 'integer':
                    case 'text':
                    case 'timestamp':
                    case 'time':
                        break;
                    case 'select':
                        listeOptions = listeOptions.concat(['contenu']);
                        break;
                    case 'list':
                        listeOptions = listeOptions.concat(['lines', 'contenu']);
                        break;
                    case 'execScreen':
                        listeOptions = ['nom', 'variable', 'index'];
                        break;
                    case 'menu':
                        listeOptions = ['nom'];
                        break;
                    case 'execProg':
                        listeOptions = ['nom', 'params'];
                        break;
                    default:
                        listeOptions = false;
                        break;
                }
                if (listeOptions !== false)
                {
                    cont = creerTableauOptions(listeOptions, valOptions);
                    $('#<?php echo $this->form['option_type_widget']->getId() ?>').hide();
                }
                else
                {
                    cont = '';
                    $('#<?php echo $this->form['option_type_widget']->getId() ?>').show();
                }
                $('#table_options').append(cont);
            }
        </script>
        <!--
        <fieldset>
          <legend>Assignation</legend>
        <?php// echo $this->form->renderFormHeader() ?>
            <table class="formulaire">
              <tr id="t_type_champ_id">
                <th style="width: 20ex;">Type de champ</th>
                <td><?php //echo $this->form['type_champ'] ?></td>
              </tr>
              <tr id="t_type_widget_id">
                <th style="width: 20ex;">Type de widget</th>
                <td><?php //echo $this->form['type_widget'] ?></td>
              </tr>
              <tr>
                <th style="width: 20ex;">Nom de la variable</th>
                <td><?php //echo $this->form['nom_champ'] . $this->form['nom_champ']->renderErrors() . $this->form['nom_champ_txt'] . $this->form['nom_champ_txt']->renderErrors() ?></td>
              </tr>
              <tr id="t_libelle_id">
                <th style="width: 20ex;">Libell&eacute;</th>
                <td><?php //echo $this->form['libelle'] ?></td>
              </tr>
              <tr id="t_option_type_widget_id">
                <th style="width: 20ex;">Options</th>
                <td><div id="table_options"></div><?php //echo $this->form['option_type_widget'] ?></td>
              </tr>
              <tr id="t_est_lecture_seule_id">
                <th style="width: 20ex;">Lecture seule</th>
                <td><?php //echo $this->form['est_lecture_seule'] ?></td>
              </tr>
              <tr id="t_formule_calcul">
                <th>Formule de calcul<br /><?php// echo new mdWidgetButtonHtmlPopup('aide', 'aide/aideFormule.php') ?></th>
                <td>
        <?php// echo $this->form['formule_calcul'] ?><br />
                  <strong>calcul systematique ? </strong><?php echo $this->form['calcul_systematique'] ?><br />
                  (si a oui les formule sont recalculée a chaque fois, sinon seulement lors de la creation d'un nouvel enregistrement.<br />
                  Dans le cas des ecrans sans table associé c'est toujours recalculé)
                </td>
              </tr>
            </table>
        <?php// echo $this->form->renderButtons() . $this->form['op']; ?>
          </form>
        </fieldset>
        -->
        
        <?php echo $this->form->start() ?>
        <table class="formulaire">
        </table>
        <table>
            <tr id="t_type_champ_id">
              <th style="width: 20ex;">Type de champ</th>
              <td><?php echo $this->form['type_champ'] ?></td>
            </tr>
            
            <tr>
                <th>Nom variable</th>
                <td>
                    <?php echo $this->form['nom_champ'] . $this->form['nom_champ']->renderErrors() . $this->form['nom_champ_txt'] . $this->form['nom_champ_txt']->renderErrors() ?>
                </td>
            </tr>
            <tr>
                <th>Origine</th>
                <td>
                    <?php //echo $this->form['patron'].$this->form['patron']->renderErrors() ?>
                </td>
            </tr>
        </table>
        <div id="onglets">
            <ul>
                <li><a href="#onglet-opt">Options widget</a></li>
                <li><a href="#onglet-par">Parametres</a></li>
                <li><a href="#onglet-for">Formules</a></li>
                <li><a href="#onglet-js">Javascript</a></li>
                <li><a href="aide/aideWidget.html">Aide</a></li>
            </ul>
            <div id="onglet-opt">
                <table>
                    <tr id="t_type_var_id">
                        <th>Type de champ</th>
                        <td><?php // echo $this->form['type_var'] ?></td>
                    </tr>
                    <tr id="t_type_widget_id">
                        <th>Type de widget</th>
                        <td><?php echo $this->form['type_widget'] ?></td>
                    </tr>
                    <tr id="t_libelle_id">
                        <th>Libell&eacute;</th>
                        <td><?php echo $this->form['libelle'] ?></td>
                    </tr>
                    <tr id="t_option_type_widget_id">
                        <th>Options</th>
                        <td><div id="table_options"></div><?php echo $this->form['option_type_widget'] ?></td>
                    </tr>
                    <tr>
                        <th>Info bulle</th>
                        <td><?php echo $this->form['info_bulle'] ?></td>
                    </tr>
                    <tr>
                        <th>Raccourci</th>
                        <td><?php echo $this->form['raccourcis'] ?><button type="button" id="eff_raccourci">Effacer</button></td>
                    </tr>
                    <tr id="t_contextuel">
                        <th>Parametre contextuel</th>
                        <td><?php echo $this->form['contextuel'] ?></td>
                    </tr>

                </table>
            </div>    
            <div id="onglet-par">
                <table>
                    <tr id="t_est_lecture_seule_id">
                        <th>Lecture seule</th>
                        <td><?php echo $this->form['est_lecture_seule'] ?></td>
                    </tr>
                    <tr>
                        <th>Obligatoire</th>
                        <td><?php echo $this->form['est_notnull'] ?></td>
                    </tr>
                    <tr>
                        <th>Valeur par defaut</th>
                        <td><?php echo $this->form['val_def'] ?></td>
                    </tr>
                    <tr>
                        <th>Valeur min</th>
                        <td><?php echo $this->form['val_min'] ?></td>
                    </tr>
                    <tr>
                        <th>Valeur max</th>
                        <td><?php echo $this->form['val_max'] ?></td>
                    </tr>
                    <tr>
                        <th>Class css</th>
                        <td><?php echo $this->form['css_class'] ?></td>
                    </tr>
                    <tr>
                        <th>Attributs css</th>
                        <td><?php echo $this->form['css_attr'] ?></td>
                    </tr>
                </table>
            </div>    
            <div id="onglet-for">
                <table>
                    <tr id="t_formule_calcul">
                        <th>Formule de calcul<br /><?php echo new mmWidgetButtonHtmlPopup('aide', 'aide/aideFormule.php') ?></th>
                        <td>
        <?php echo $this->form['formule_calcul'] ?>
                        </td>
                    </tr>
                </table>
                <div>
                    <strong>calcul systematique ? </strong><?php echo $this->form['calcul_systematique'] ?><br />
                    si a oui les formule sont recalculée a chaque fois, sinon seulement lors de la creation d'un nouvel enregistrement.<br />
                    Dans le cas des ecrans sans table associé c'est toujours recalculé
                </div>
            </div>    
            <div id="onglet-js">
                <table>
                    <tr>
                        <th>Click</th>
                        <td><?php echo $this->form['jsclick'] ?></td>
                    </tr>
                    <tr>
                        <th>Prise de focus</th>
                        <td><?php echo $this->form['jsfocus'] ?></td>
                    </tr>
                    <tr>
                        <th>Perte de focus</th>
                        <td><?php echo $this->form['jsblur'] ?></td>
                    </tr>
                    <tr>
                        <th>Changement après perte de focus</th>
                        <td><?php echo $this->form['jschange'] ?></td>
                    </tr>
                    <tr>
                        <th>Double click</th>
                        <td><?php echo $this->form['jsdblclick'] ?></td>
                    </tr>
                    <tr>
                        <th>Click droit</th>
                        <td><?php echo $this->form['jsrclick'] ?></td>
                    </tr>
                </table>
            </div>    
        </div>

        <?php echo $this->form->renderButtons() . $this->form['op']; ?>
        <?php echo $this->form->stop() ?>

        
        <?php
        //rendu du javascript
        echo $this->form->renderJavascript();
        ?>
        <script type="text/javascript">
            $(document).ready(function()
            {
                $('#<?php echo $this->form['option_type_widget']->getId() ?>').keydown(function(event){
                    if (event.which == 112)
                    {
                        openWindow('aide/aideWidget.php');
                        event.stopPropagation();
                        event.preventDefault();
                    }
                });
                $('#<?php echo $this->form['type_widget']->getId() ?>').change(function(){
                    initialiseOptions();
                });
                majAffichage();
                initialiseOptions();
            });

        </script>
        <?php
    }

}
?>
