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
@file : pEcran.php
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



/**
 * 
 */
class pEcran extends mmProg {

    public function preExecute(mmRequest $request) {
        if (!mmUser::superAdmin()) {
            mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
            $this->redirect(url('@home'));
        }
    }
    /**
     * Nettoyage de la session et redirection vers l'editeur
     * @param mmRequest $request
     */
    public function executeIndex(mmRequest $request) {
        //On recupere la liste des ecrans

        mmSession::remove('__editionNomEcran__');
        $this->redirect(url('pEcran/edit'));
    }

    public function executeCreate(mmRequest $request) {
        //On defini quel fond d'ecran html on veux utiliser
        $this->setTemplate('view_pEcran_editEcran');
        //On charge en memoire un nouveau champ vide de la ecran 'Tables' (appelée T101 dans la base)
        $ecran = new EcranUtilisateur();
        //Initialisation du formulaire
        $this->initFormEcran($ecran);
    }

    public function executeCreerEcran(mmRequest $request) {
        $this->executeCreate($request);
        $this->enregistreEcran($request);
    }

    public function executeSupprimeEcran(mmRequest $request) {
        mmUser::flashInfo('LE CODE DE SUPRESSION DE TABLE EST APPELE');
        //On charge l'enregistrement
        $nomEcran = $request->get('ecran', false);
        if ($nomEcran) {
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
            $champsEcran = $ecran->get('ChampsEcranUtilisateur');
            $champsEcran->delete();
            $ecran->delete();
            mmUser::flashSuccess('Suppresion OK');
        } else {
            mmUser::flashError('Aucun nom d\'ecran fournis');
        }
        $this->redirect(url('pEcran'));
    }

    public function executeEdit(mmRequest $request) {
        //On defini quel fond d'ecran html on veux utiliser
        $this->setTemplate('view_pEcran_editEcran');
        //On verifie si le nom de ecran exist dans la cession. dans ce cas la on le prend, sinon on prend celui fournis en parametre

        $nomEcran = $request->get('ecran', false);
        if (!$nomEcran) {
            $nomEcran = mmSession::get('__editionNomEcran__', false);
            if (!$nomEcran) {
                //ici on signal que c'est un appel de premiere main
                $nomEcran = false;
//        throw new mdExceptionControl("Aucun ecran de travail definie");
            }
        } else {
            mmUser::set('__editionNomEcran__', $nomEcran);
        }
        //on charge la ligne de description de la ecran utilisateur depuis la base
        //on a pas d'ecran on prend le premier sinon on prend celui defini par nomEcran
        if ($nomEcran === false) {
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->createQuery()->limit(1)->fetchOne();
            if (!$ecran) {
                mmUser::flashError('Aucune écran n\'a été créé. Vous avez été positionné sur la création');
                $this->redirect(url('pEcran/create'));
            } else {
                $nomEcran = $ecran['nom_ecran'];
                mmUser::set('__editionNomEcran__', $nomEcran);
            }
        } else {
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($nomEcran);
        }

        if (!$ecran) {
            mmUser::flashError("L'ecran $nomEcran est introuvable");
            $this->redirect(url('pEcran/create'));
        }

        //Initialisation du formulaire
        $this->initFormEcran($ecran);
    }

    public function executeMajEcran(mmRequest $request) {
        $this->executeEdit($request);
        $this->enregistreEcran($request);
    }

    public function enregistreEcran(mmRequest $request) {
        $dataRecut = $request->get($this->form->getName(), false);

        if ($dataRecut) {
            //On a recut des données ? dans ce cas la on enregistre
            $valide = $this->verifieSaisie($dataRecut); // verification maison
            if ($this->form->setValues($dataRecut) && $valide) { //Validation et assignation des données recut 
                $ecran = $this->form->save();  //Enregistrement dans la base
                //on effectue le nettoyage de la liste des champs
                $regex = '#[\$@](\w+)#'; //Expression pour la compilation des variables
                $listeVariables = array();
                $cible = preg_match_all($regex, $ecran['template'], $listeVariables);
                //on construit le tableau final des variable
                $listeVarFinal = array();
                foreach ($listeVariables[1] as $variable) {
                    $listeVarFinal[] = $variable;
                }
                //On nettoie
                $rq = Doctrine_Core::getTable('ChampsEcranUtilisateur')->createQuery()->
                        delete()->
                        whereNotIn('nom_champ', $listeVarFinal)->
                        andWhere('nom_ecran = ?', $ecran['nom_ecran'])->
                        execute();
                //on a fini on rappel la page d'edition pour afficher le formulaire a jour
                mmUser::set('__editionNomEcran__', $ecran['nom_ecran']);
                $this->redirect(url('pEcran/edit'));
//<----- on sort en allant a la page index.php?module=pEcran&action=editEcran       
            } else {
                // y'a des erreur
            }
        } else {
            //Pas de données recut ?
            //On fait rien, pour le moment
        }
    }

    public function verifieSaisie($dataRecut) {
        $valide = true;

        //verification de l'existance de la table lié
        if ($dataRecut['table_liee'] != '') {
            $tableLie = Doctrine_Core::getTable('TableUtilisateur')->find($dataRecut['table_liee']);
            if (!$tableLie) {
                //la table utilisateur n'est pas trouvé c'est une erreur
                try {
                    $this->form['table_liee']->addError('La table selectionnée n\'existe pas');
                } catch (mmExceptionWidget $e) {
                    //rien
                }
                $valide = false;
            }
        }

        //Verifie qu'on ai pas un ecran du meme nom dans le cas d'un nouvel ecran
        if ($this->form->isNew()) {
            $ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($dataRecut['nom_ecran']);
            if ($ecran) {
                //on a un ecran du meme nom
                try {
                    $this->form['nom_ecran']->addError('Un ecran du meme nom existe deja');
                } catch (mmExceptionWidget $e) {
                    //rien
                }
                $valide = false;
            }
        }
        return $valide;
    }

    public function initFormEcran($ecran) {
        //initialisation des variables
        //creation du formulaire avec les parametre de base
        $this->form = new mmForm($ecran);
        //Personalisation du formulaire
        //On force la cle unique a s'afficher (par defaut ce champs est caché)
        
        // setup the template name widget
        // todo : dynamicly create template list, for now only 2 two standard entries to debug.
        $templateList = array(
            'layout' => 'layout',
            'popup'  => 'popup'
        );
//        $this->form->addWidet(new mdWidgetSelect($this->form['template_name'], $templateList, $ecran['template_name']));
        
        if ($ecran->exists()) { // $ecran est un enregistrement existant dans la base de données
        // dans ce cas la on est en edition, on construit l'interface en conséquence
            $listeEcransBase = Doctrine_Core::getTable('EcranUtilisateur')->createQuery()->
                    select('nom_ecran')->
                    orderBy('nom_ecran')->
                    execute();
            $listeEcranSelect = array();
            foreach ($listeEcransBase as $ecranCourant) {
                $listeEcranSelect[$ecranCourant['nom_ecran']] = $ecranCourant['nom_ecran'];
            }
            $this->form->addWidget(new mmWidgetSelect($this->form['nom_ecran'], $listeEcranSelect, '', array('onchange' => "goPage('".url('pEcran/edit')."/'+$(this).val())")));
            $this->form->addWidget(new mmWidgetButtonGoPage('Creer un ecran', url('pEcran/create')), false, 'nouveau');
            $this->form->addWidget(new mmWidgetButton('supprimer', "supprimer l'écran"));
//      $this->form->addWidget(new mdWidgetButton('supprimer', "supprimer l'écran", array('onclick'=>"if (confirm('Voulez vous supprimer cet ecran ?')) goPage('?module=pEcran&action=supprimeEcran&ecran=".$ecran['nom_ecran']."')")));
//      $this->form->addWidget(new mdWidgetButtonAjaxPopup('Générer le programme', '?module=pEcranGenereCRUD', 'genere'));
            $this->form->addWidget(new mmWidgetButtonHtmlPopup('Editer javascript', url('pEcranJS/editer?nomEcran='.$ecran['nom_ecran']), 'editJS'));
            $this->form->addWidget(new mmWidgetButtonHtmlPopup('Editer déclaration', url('pEcranDec/editer?nomEcran='.$ecran['nom_ecran']), 'editDec'));
            $this->form->addWidget(new mmWidgetButtonAjaxPopup('Copier l\'écran', url('pEcranCopie?a='.$ecran['nom_ecran']), 'copieEcran'));
            $this->form->addWidget(new mmWidgetButtonAjaxPopup('Genere le programme', url('pModuleGenerator?sn='.$ecran['nom_ecran']), 'generateModule'));
        } else {
            // c'est un nouvel ecran, on met une zone de saisie
            $this->form->addWidget(new mmWidgetText($this->form['nom_ecran']));
            //widgets vide pour assurer la cohérence du formulaire
            $this->form->addWidget(new mmWidgetBlank('nouveau'));
            $this->form->addWidget(new mmWidgetBlank('supprimer'));
            $this->form->addWidget(new mmWidgetBlank('editJS'));
            $this->form->addWidget(new mmWidgetBlank('copieEcran'));
           
        }

        //preparation de la liste des tables
        $listeTablesUtilisateur = Doctrine_Core::getTable('TableUtilisateur')->createQuery()->
                select('nom_table')->
                orderBy('nom_table')->
                execute();
        $listeTablesSelect = array('' => '-'); //tableau avec une ligne vide pour commencer
        foreach ($listeTablesUtilisateur as $tableCourante) {
            $listeTablesSelect[$tableCourante['nom_table']] = $tableCourante['nom_table'];
        }
        $this->form->addWidget(new mmWidgetSelect($this->form['table_liee'], $listeTablesSelect));

        //Preparation de l'editeur richEdit
//    $this->form->addWidget(new mdWidgetTinyMce($this->form['template']));
//    $this->form->addWidget(new mdWidgetCKEditor($this->form['template']));
        switch ($ecran['mode_rendu']) {
            case 'htm':
                $this->form->addWidget(new mmWidgetCKEditor($this->form['template'], ''));
                break;
            //TODO: ajouter le support du $ ou du F10 dans l'edition du code source
            case 'src_AJOUTER_SUPPORT_F10':
                $this->form->addWidget(new mmWidgetCodeMirror($this->form['template'], ''));
                break;
            default:
                $this->form->addWidget(new mmWidgetTextArea($this->form['template'], '', array('cols' => 150, 'rows' => 40)));
                break;
        }
//    if ($ecran['mode_rendu'] == "htm")
//    {
//      $this->form->addWidget(new mdWidgetCKEditor($this->form['template'], ''));
//    }
//    else
//    {
//      
//    }
        //Gestion du choix du mode d'entree
        $this->form->addWidget(new mmWidgetSelect($this->form['mode_rendu'], array('txt' => 'SIMPLE', 'htm' => 'HTML', 'src' => 'Code Source')));
        //Ajout de la destination
        $this->form->addWidget(new mmWidgetSelect($this->form['destination'], array('scr' => 'Ecran', 'imp' => 'Impression')));
        //Bouton qui ouvre une popup
        //creation de la list des champs

        $nomTable = $ecran['table_liee'];
        if ($nomTable) {
            //Si on a une table lié on va chercher les champs existants
            //On commence par les champs de la table existante
            $table = Doctrine_Core::getTable($nomTable);
            $colonnes = array();
            foreach ($table->getColumnNames() as $colonneCourante) {
                $colonnes[$colonneCourante] = $colonneCourante;
            }
            //On parcourt maintenant les champs de table utilisateur
            $descriptionCols = Doctrine_Core::getTable('ChampsTableUtilisateur')->findByNomTable($nomTable);
            foreach ($descriptionCols as $colonneCourante) {
                $colonnes[$colonneCourante['nom_champ']] = $colonneCourante['nom_champ'];
            }
        } else {
            $colonnes = array();
        }
        //On cherche les champs deja decris dans la base de données des champ d'écran. Ceci n'est valable seulement
        //que si c'est un enregistrement deja enregistré
        if ($ecran->exists()) {
            $descriptionCols = Doctrine_Core::getTable('ChampsEcranUtilisateur')->findByNomEcran($ecran['nom_ecran']);
            foreach ($descriptionCols as $colonneCourante) {
                $colonnes[$colonneCourante['nom_champ']] = '* ' . $colonneCourante['nom_champ'];
            }
        }

        $this->form->addWidget(new mmWidgetList('liste_champ', $colonnes, '', array('size' => 10)));
        //bouton d'operation sur les champs
//    $bouton = new mdWidgetButton('edit_champ', 'Editer le champ');
//    $bouton->click('editAttribut()');
//    $this->form->addWidget($bouton);
//    $this->form->addWidget(new mdWidgetButtonAjaxPopup('Ajouter champ', '?module=pEcranPopup&action=nouveau'));
        //On ajoute les boutons de validations et de retour
        $this->form->addWidget(new mmWidgetButton('retour', 'Retour', array('onclick' => "goPage('" . genereUrlProtege('?') . "')")));
//    $this->form->addWidget(new mdWidgetButton('affecter', 'Faire l\'affectation', array('onclick'=>'assignVar()')));
        $this->form->addWidget(new mmWidgetButton('valider', 'Enregistrer', array('onclick' => 'submit()')));
        $this->form->addWidget(new mmWidgetButtonHtmlPopup('Apercu', '?module=pEcranAppercu&action=nouveau'));
    }

    public function initFormColonne($description = array()) {
        //Variables
        $typeColonne = array(
            'integer' => 'integer',
            'string' => 'string',
            'time' => 'time',
            'timestamp' => 'timestamp',
            'date' => 'date',
            'blob' => 'blob'
        );
        $ouiNon = array('0' => 'Non', '1' => 'Oui');

        //definition des parametres du sous formulaire
        $formColonne = new mmForm($description);
        $formColonne->addWidget(new mmWidgetSelect($formColonne['type_data'], $typeColonne));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['est_primary'], $ouiNon));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['est_autoincrement'], $ouiNon));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['est_notnull'], $ouiNon));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['est_index'], $ouiNon));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['recherche'], $ouiNon));
        $formColonne->addWidget(new mmWidgetSelect($formColonne['ro'], $ouiNon));

        return $formColonne;
    }

}

?>
