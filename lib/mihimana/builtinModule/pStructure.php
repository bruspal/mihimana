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
@file : pStructure.php
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


//entete classic pour un programme

class pStructure extends mmProg {
  
  /**
   * Liste de selection des tables existante dans le model
   * @param type $request 
   */
  public function preExecute(mmRequest $request)
  {
    if ( ! mmUser::superAdmin())
    {
      mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
      $this->redirect('?');
    }
  }
  
  public function executeIndex(mmRequest $request) {
    //On recupere la liste des tables
    
    $tables = Doctrine_Core::getTable('TableUtilisateur')->createQuery()->orderBy('nom_table')->execute();
    
    $listeTables = array();
    foreach ($tables as $table) {
      $listeTables[$table['nom_table']] = $table['nom_table'];
    }
    $this->listeTables = $listeTables;
    //Creation du formulaire
    $this->form = new mmForm();
    $this->form->addWidget(new mmWidgetButtonGoModule('Retour'));
    $this->form->addWidget(new mmWidgetButtonGoModule('Creer nouvelle Table', 'pStructure', 'nouvelleTable'));
  }
  
  public function executeEditChamps(mmRequest $request)
  {
    $this->setTemplate('html_pStructure_editChamp');
//    $this->setLayout('popup');
    
    $nomTable = $request->getParam('table', false);
    if (! $nomTable) {
      //c'est pas fournis en parametres ? on va chercher dans la session
      $nomTable = mmSession::get('__structureNomTable__', false);
      //Gestion d'erreur avec exception
      if (! $nomTable) {
        throw new mmExceptionControl('Aucune table de travail definie');
      }
    }
    else
    {
      mmSession::set('__structureNomTable__', $nomTable);
    }
    //recuperation dans la base
    //chargement de la table utilisateur concerné. Utile pour la suite a creer le lien entre le champ et la table
    $this->table = Doctrine_Core::getTable('TableUtilisateur')->find($nomTable); //Table de reference recuperé a partir de son index
//    $this->colonnes = $this->table->get('ChampsTableUtilisateur');
    $this->colonnes = Doctrine_Core::getTable('ChampsTableUtilisateur')->createQuery()->
            where('nom_table = ?', $nomTable)->
            execute();
    $this->form = new mmForm();
    $this->form->addWidget(new mmWidgetButtonSubmit());
    $this->form->addWidget(new mmWidgetButton('retour', 'Retour', array('onclick'=>"goPage('?module=pStructure')")));
    $this->form->addWidget(new mmWidgetButton('nouveau', 'Nouveau', array('onclick'=>"openWindow('?module=pStructurePopup&action=nouveau&table=$nomTable')")));
//    $this->form->addWidget(new mdWidgetButtonAjaxPopup('Nouveau', "?module=pStructurePopup&action=nouveau&table=$nomTable"));
  }
  
  public function _executeEditChamps(mmRequest $request) {
    //initialisation
//    $listColonne = array();
    //On dit quel model utilisé
    $this->setTemplate('html_pStructure_editChamp');

    //recuperation des parametres
    $nomTable = $request->getParam('table', false);
    if (! $nomTable) {
      //c'est pas fournis en parametres ? on va chercher dans la session
      $nomTable = mmSession::get('nomTable', false);
      //Gestion d'erreur avec exception
      if (! $nomTable) {
        throw new mmExceptionControl('Aucune table de travail definie');
      }
    }
    //recuperation dans la base
    //chargement de la table utilisateur concerné. Utile pour la suite a creer le lien entre le champ et la table
    $table = Doctrine_Core::getTable('TableUtilisateur')->find($nomTable); //Table de reference recuperé a partir de son index
    
    //Differente methode pour recuperer les colonne defini dans la table
     //recupere la liste des champs dans l'ordre de definition en utilisant findBy*
//    $colonnes = Doctrine_Core::getTable('ChampsTableUtilisateur')->findByNomTable($nomTable);
    //en creant et executant une requete, retourne les resultat trie par nom du champs
//    $colonnes = Doctrine_Core::getTable('ChampsTableUtilisateur')->createQuery()->where('nom_table = ?', $nomTable)->orderBy('nom_champ')->execute();
    //en utilisant le model relationnel: les champs de la table sont lié par une relation 1-n, on utilise la possibilite de table de retourner ses enregistrements liés
    $colonnes = $table->get('ChampsTableUtilisateur');
// TODO: verifier le parametrage de doctrine pour voir pourquoi le geter marche pas    
    
    //premier appel ?
//    if (mdSession::get('editChampRetour', false)) {
    if (count($_POST) > 0) { //ici on se base sur la presence de donner retourné en POST pour savoir si c'est la premiere fois ou non
      //c'est un retour: on assign et on sauve
      //premiere generation du formulaire pour retrouver la configuration initiale
      $this->form = $this->initFormEditChamps($table, $colonnes);
      //recuperation des données du formulaire
      $data = $request->getParam('form');
      //si le nom du nouveau champ est vide, on le retire des données avant la suite du traitement
      if (trim($data['__nouveauChamp__']['nom_champ']) == '') {
        unset ($data['__nouveauChamp__']);
        unset ($this->form->formsList['__nouveauChamp__']);
      }
      //assignation
      if ($this->form->setValues($data)) { //assignation
        //reussis ? on enregistre
        /*
         * /!\ En attendant le debug on sauve a l'ancienne /!\
         * 
         */ 
         $this->form->save();
         /*
         */
        
//        foreach ($this->form->formsList as $sousForm) {
//          $sousForm['nom_table'] = $table['nom_table'];
//          $sousForm->save();
//        }
        //on a fini de sauver on reaffiche le formulaire pret a saisir en retournant a l'ecran d'edition
        User::flashSuccess('La collone a été ajoutée') ;
        $this->redirect('?module=pStructure&action=editChamps');
      }
      else {
        //sinon on reagit en consequence
        User::flashError('Erreur lors de la creation de la colonne');
      }
      
    }
    else {
      //Oui c'est un 1er appel: on initialise le formulaire et on dit qu'on viensd'arriver pour la premiere fois
      //Une autre option est de verifier qu'on a des données en POST, si oui c'est un retour
      $this->form = $this->initFormEditChamps($table, $colonnes);
      mmSession::set('editChampRetour', true);
      mmSession::set('nomTable', $nomTable);
    }
  }

  public function initFormEditChamps($table, Doctrine_Collection $colonnes, $ajoutChampVide = true) {
    //creation du formulaire general
    $formGeneral = new mmForm();
    $formGeneral->addWidget(new mmWidgetButton('valider', 'Valider', array('onclick'=>"submit()")));
    $formGeneral->addWidget(new mmWidgetButton('annuler', 'Annuler', array('onclick'=>"goPage('?module=pStructure')")));

    //On construit la liste des formulaire pour les colonnes
    foreach ($colonnes as $descriptionChamp) {
      $formGeneral->addForms($this->initFormColonne($descriptionChamp), $descriptionChamp['nom_champ']);
    }
    
    //On construit un nouveau champ vide si c'est demandé
    if ($ajoutChampVide) {
      $nouveau = new ChampsTableUtilisateur();
      $nouveau->set('TableUtilisateur', $table);
      //a l'ajoute a la collection de données
      $colonnes->add($nouveau);
      $formGeneral->addForms($this->initFormColonne($nouveau), '__nouveauChamp__');
      
    }
    
    return $formGeneral;
  }
  
  public function executeNouvelleTable(mmRequest $request) {
    //On defini quel fond d'ecran html on veux utiliser
    $this->setTemplate('html_pStructure_editTable');
    //On charge en memoire un nouveau champ vide de la table 'Tables' (appelée T101 dans la base)
    $table = new TableUtilisateur();
    //Initialisation du formulaire
    $this->initFormTable($table);
    //Apres ce point la page html_pStructure_editTable.php va etre executée
  }
  
  public function executeCreerTable(mmRequest $request) {
    $this->executeNouvelleTable($request);
    $this->enregistreTable($request);
  }
  
  public function executeSupprimeTable(mmRequest $request) {
    User::flashInfo('LE CODE DE SUPRESSION DE TABLE EST APPELE');
    //on supprime les champs associé
    $nomTable = $request->getParam('table', false);
    if ($nomTable)
    {
      //Suppression des champs
      $champs = Doctrine_Core::getTable('ChampsTableUtilisateur')->findByNomTable($nomTable);
      $champs->delete();
      //Suppression de la table
      $table = Doctrine_Core::getTable('TableUtilisateur')->find($nomTable);
      if ($table)
      {
        $table->delete();
      }
      else
      {
        User::flashWarning("Table inexistante. Ce n'est pas grave, ca empeche rien");
      }
      User::flashSuccess("Suppression effectuée");
    }
    else
    {
      User::flashError('Parametre manquant: opération abandonnées');
    }
    $this->redirect('?module=pStructure');
  }
  
  public function executeEditTable(mmRequest $request) {
    //On defini quel fond d'ecran html on veux utiliser
    $this->setTemplate('html_pStructure_editTable');
    //On verifie si le nom de table exist dans la cession. dans ce cas la on le prend, sinon on prend celui fournis en parametre
    $nomTable = $request->getParam('table', false);
    if ( ! $nomTable)
    {
      $nomTable = mmSession::get('nomTable', false);
      if ( ! $nomTable)
      {
        mmUser::flashError('Table introuvable');
        $this->redirect('?module=pStructure');
      }
    }
    else {
      mmSession::set('nomTable', $nomTable);
    }
    //on charge la ligne de description de la table utilisateur depuis la base
    $table = Doctrine_Core::getTable('TableUtilisateur')->find($nomTable);
    //Initialisation du formulaire
    $this->initFormTable($table);
    //Apres ce point la page html_pStructure_editTable.php va etre executée
  }
  
  public function executeMajTable(mmRequest $request) {
    $this->executeEditTable($request);
    $this->enregistreTable($request);
  }
  
  public function enregistreTable(mmRequest $request) {
    $dataRecut = $request->getParam($this->form->getName(), false);
    if ($dataRecut) {
      //On a recut des données ? dans ce cas la on enregistre
      if ($this->form->setValues($dataRecut)) { //Validation et assignation des données recut
        //les données sont valide
//        try {
          $this->form->save();  //Enregistrement dans la base
          //on a fini on rappel la page d'edition pour afficher le formulaire a jour
          $this->redirect("?module=pStructure");
//        }
//        catch (Exception $e) {
//          echo "<h1>ERREUR</h1>".$e->getMessage();  //En cas de plantage grave on affiche le message
//        }
      }
      else {
        //les données sont invalide
        echo "<h1>Erreur de saisie</h1>";
        //ici le script continu en reaffichant la meme page
      }
    }
    else {
      //Pas de données recut ?
      //On fait rien, pour le moment
    }
  }
  
  public function initFormTable($table) {
    //initialisation des variables
    $emplacement = array(
//        'param'=>'Parametres', desactivé pour le moment
        'data'=>'Données de travail'
    );
    
    //creation du formulaire avec les parametre de base
    $this->form = new mmForm($table);
    //Personalisation du formulaire
    //On force la cle unique a s'afficher (par defaut ce champs est caché)
    $this->form->addWidget(new mmWidgetText($this->form['nom_table']));
    $this->form->addWidget(new mmWidgetSelect($this->form['emplacement'], $emplacement));
    //On ajoute les boutons de validations et de retour
    $this->form->addWidget(new mmWidgetButton('valider', 'Enregistrer', array('onclick'=>'submit()')));
    $this->form->addWidget(new mmWidgetButton('retour', 'Annuler', array('onclick'=>"goPage('?module=pStructure')")));
    
  }
  
  public function initFormColonne($description = array()) {
    //Variables
    $typeColonne = array(
        'integer'=>'integer',
        'string'=>'string',
        'time'=>'time',
        'timestamp'=>'timestamp',
        'date'=>'date',
        'blob'=>'blob'
    );
    $ouiNon = array('0'=>'Non', '1'=>'Oui');

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
