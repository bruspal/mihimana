<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmProgs
  @file : mmProgCRUD.php
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

class mmProgCRUD extends mmProg {

    public
            $tableName = false,
            $screenName = false,
            $options = array(
                'genereIndex' => true,
                'genereDelete' => true,
                'autoHtml' => true,
                'cols' => array(),
                'condition' => '',
                'addDelete' => false
    );
    protected
            $table,
            $ecran;

    public function __construct() {
//    $this->options = array (
//        'genereIndex' => true,
//        'genereDelete' => true,
//        'autoHtml' => true
//    );
//    $this->nomTable = false;
//    $this->nomEcran = false;

        parent::__construct();
    }

    //Gestion de la configuration de base
    public function configure(mmRequest $request) {
        parent::configure($request);

        //La table de travail est obligatoire
        if (!($this->tableName or $this->screenName)) {
            throw new mmExceptionDev("Aucune table/ecran declaree pour la generation de l'ecran");
        }

        //Le nom d'ecran est optionel
    }

    //Index par defaut ou que faire si on veux pas d'ecran index
    public function executeIndex(mmRequest $request) {
        $this->setTemplate(false);
        if ($this->options['genereIndex']) {
            $this->initEcranTable();
            if ($this->table) {
                //On construit le formulaire par defaut
                $form = new mmForm();
                $liste = new mmWidgetRecordList('listeCRUD', $this->tableName, "goPage('?module=" . MODULE_COURANT . "&action=editer&b=%s')", $this->options['condition'], array(
                    'lines' => 10,
                    'cols' => $this->options['cols'],
                    'nom' => 'listeCRUD'
                ));
                $form->addWidget($liste);
                $form->addWidget(new mmWidgetButtonGoPage('Nouveau', genereUrlProtege('?module=' . MODULE_COURANT . '&action=nouveau')));
                $form->addWidget(new mmWidgetButtonGoModule('Retour'));
                echo $form->renderButtons();
                echo $form['listeCRUD'];
            } else {
                throw new mmExceptionControl('Failed to auto generate index list. undefined table');
            }
        } else {
            if ($this->screenName) {
                $form = new mmScreen($this->screenName, $this->table);
                echo $form->render();
                echo $form->renderJavascript();
            } else {
                echo "Generation de la liste desactivee";
            }
        }
    }

    public function executeNouveau(mmRequest $request, $afficherFormulaire = true) {
        $this->initEcranTable();

        $nomTable = $this->tableName;
        if ($nomTable) {
            $this->table = new $nomTable;
        } else {
            $this->table = new mmDoctrineRecordVide();
        }

        $this->initForm($this->table, true);
        if ($afficherFormulaire) {
            $this->afficheFormulaire();
        }
    }

    public function executeCreer(mmRequest $request) {
        $this->executeNouveau($request, false);
        $this->enregistrer($request);
    }

    public function executeEdit(mmRequest $request, $afficherFormulaire = true) {
        $this->initEcranTable();

        if ($this->tableName) {
//      $nomIndex = Doctrine_Core::getTable($this->nomTable)->getIdentifierColumnNames();
            $chaineIndex = $request->getParam('b', false);
            if ($chaineIndex == false) {
                throw new mmExceptionControl("Cle de recherche non fournie");
            }
            $index = mmSQL::genereIndex($chaineIndex);
            $this->table = Doctrine_Core::getTable($this->tableName)->find($index);
//      $this->table = Doctrine_Core::getTable($this->nomTable)->find($request->getParam($nomIndex[0], false));
//      $this->table = Doctrine_Core::getTable($this->nomTable)->find($request->getParam('id', false));
        }
        $this->initForm($this->table, false);
        if ($afficherFormulaire) {
            $this->afficheFormulaire();
        }
    }

    public function executeUpdate(mmRequest $request) {
        $this->executeEdit($request, false);
        $this->enregistrer($request);
    }

    public function initForm(Doctrine_Record $table, $nouveau) {
        if ($this->screenName) {
            //On initialise un ecran
            $form = new mmScreen($this->screenName, $table);
        } else {
            //C'est un formulaire standard
            $form = new mmForm($table);
            $form->addWidget(new mmWidgetButtonSubmit('Enregistrer'), true);
            $form->addWidget(new mmWidgetButtonGoPage('Precedent', genereUrlProtege('?module=' . MODULE_COURANT)), true);
            if ($this->options['addDelete'] === true && !$form->isNew()) {
                $cleEnreg = mmSQL::genereChaineIndex($this->table);
                $form->addWidget(new mmWidgetButton('supp', 'Supprimmer', array(
                    'onclick' => "if(alert('Voulez vous supprimer cet enregistrement ?')) goPage('?module=" . MODULE_COURANT . "&action=delete&b=$cleEnreg')"
                )));
            }
        }

        if ($nouveau) {
            $form->setAction(genereUrlProtege('?module=' . MODULE_COURANT . '&action=creer'));
        } else {
            $chaineCle = $this->genereChaineIndex($form->getRecord());
            $form->setAction(genereUrlProtege('?module=' . MODULE_COURANT . '&action=maj&b=' . $chaineCle));
        }


        $this->form = $form;

//    $this->nouveau = $nouveau;
    }

    public function delete(mmRequest $request) {
        if ($this->options['addDelete'] === true) {
            $chaineIndex = $request->getParam('b', false);
            if ($chaineIndex == false) {
                mmUser::flashError("Cle non fournie");
                $this->redirect('?module=' . MODULE_COURANT . '&action=index');
            }
            $index = mmSQL::genereIndex($chaineIndex);
            $this->table = Doctrine_Core::getTable($this->tableName)->find($index);
            if ($this->table === false) {
                mmUser::flashError("Enregistrement non trouvé");
                $this->redirect('?module=' . MODULE_COURANT . '&action=index');
            }
            $this->table->delete();
            $this->redirect('?module=' . MODULE_COURANT . '&action=index');
        }
    }

    public function enregistrer(mmRequest $request) {
        //On recupere les données du formulaires
        $data = $request->getParam($this->form->getName(), false);
        //On effectue l'assignation et la verification des données saisies
        if ($this->form->setValues($data)) {
            //Effectation OK on peux sauver
            $enregistrement = $this->form->save();
            User::flashSuccess('Enregistrement effectué');
            if ($this->options['genereIndex']) {
                //On a l'action index ? on retourne sur l'index
                $this->redirect('?module=' . MODULE_COURANT);
            } else {
                //sinon on reviens sur la page de l'edition
                //On commence par recuperer l'index de la table et redirige vers l'edition
                $chaineIndex = $this->genereChaineIndex($enregistrement);
                $result = $this->postSave();
                if ($resultat !== false && $resultat !== true) {
                    throw new mmExceptionDev(get_class() . "::postSave() : cette methode doit renvoyer 'true' ou 'false'.");
                }
                if ($result !== false) {
                    $this->redirect('?module=' . MODULE_COURANT . '&action=editer&' . $chaineIndex);
                }
            }
        } else {
            User::flashError('Erreur lors de la saisie');
            $this->afficheFormulaire();
        }
    }

    public function postSave() {
        //action a faire apres le save.
        //Elle doit obligatoirement retourner une valeur 'true' ou 'false'
        //si elle renvoie 'true' le traitement continu jusqu'au redirect
        //sinon le traitement est interompu et l'écran est affiché
        return true;
    }

    protected function genereChaineIndex(Doctrine_Record $enregistrement) {
        $chaineResultat = '';
        $indexTable = $enregistrement->identifier(); //retourne sous forme d'un tableau $nomIndex=>$valeurIndex
        foreach ($indexTable as $nomIndex => $valeurIndex) {
            $chaineResultat .= "&$nomIndex=$valeurIndex";
        }
        //on retire le premier '&' et on renvoie
        $chaineResultat = substr($chaineResultat, 1);
        return $chaineResultat;
    }

    /*
     * Methode de preparation du rendu
     */

    protected function afficheFormulaire($fieldList = null) {
        if ($this->options['autoHtml']) {
            echo $this->form->render($fieldList);
        }
    }

    protected function afficheFormulaireDepuisBase() {
        echo '<fieldset><legend>Edition</legend>';

        $renduEcran = new mmScreen($this->screenName, $this->table);
        if ($this->nouveau) {
            $renduEcran->setAction(genereUrlProtege('?module=' . MODULE_COURANT . '&action=creer'));
        } else {
            $chaineCle = $this->genereChaineIndex($this->form->getRecord());
            $renduEcran->setAction(genereUrlProtege('?module=' . MODULE_COURANT . '&action=maj&' . $chaineCle));
        }
        echo $renduEcran->render();
        echo "</fieldset>";
    }

    protected function afficheFormulaireStandard() {
        echo '<fieldset><legend>Edition</legend>';
        if ($this->nouveau) {
            printf('<form action="%s" method="post">', genereUrlProtege('?module=' . MODULE_COURANT . '&action=creer'));
        } else {
            $chaineCle = $this->genereChaineIndex($this->form->getRecord());
            printf('<form action="%s" method="post">', genereUrlProtege('?module=' . MODULE_COURANT . '&action=maj&' . $chaineCle));
        }
        echo $this->form;
        echo "</form></fieldset>";
    }

    protected function initEcranTable() {
        //TODO: $this->nomTable n'est pas tres utile dans ce cas la. voir effet si suppression
        if ($this->tableName) {
            $this->table = Doctrine_Core::getTable($this->tableName);
        } else {
            $this->ecran = Doctrine_Core::getTable('EcranUtilisateur')->find($this->screenName);
            if (!$this->ecran) {
//        throw new mdExceptionData("Ecran introuvable: {$this->nomEcran}");
                $ecran = new EcranUtilisateur();
            }
            $tableName = $this->ecran['table_liee'];
            if ($tableName) {
                $this->table = Doctrine_Core::getTable($tableName);
                if (!$this->table) {
                    //On a pas trouvé la table lié, on signale l'erreur
                    throw new mmExceptionControl(sprintf("l'écran %s fait référence à la table %s. Or cette table n'existe pas ou n'a pas été trouvée"), $this->screenName, $this->tableName);
                }
                $this->tableName = $tableName;
            } else {
                //il n'y a pas de table lié
                $this->tableName = false;
                $this->table = null;
            }
        }
    }

}