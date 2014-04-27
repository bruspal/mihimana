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
@file : pGestionBase.php
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



class pGestionBase extends mmProg {

    public function preExecute(mmRequest $request) {
        if (!mmUser::superAdmin()) {
            mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
            $this->redirect(url('@home'));
        }
    }

    public function executeIndex(mmRequest $request) {
        echo '<h1>Options</h1>';
        if (MODE_INSTALL) {
            echo new mmWidgetButton('creerDB', 'Creer la base de données de parametres', array('onclick' => "if (confirm('CECI VA DETRUIRE LA BASE ETES VOUS SUR DE VOULOIR CONTINUER ?')) goPage('".url('pGestionBase/createDBParam')."')")) . '<br />';
            echo new mmWidgetButtonGoModule('Dumper les data de la base de parametres', 'pGestionBase', 'dumpData') . '<br />';
            echo new mmWidgetButtonGoModule('Charger les data de la base de parametres', 'pGestionBase', 'loadData') . '<br />';
        }
        echo new mmWidgetButtonGoModule('Genere la base de données utilisateur', 'pGestionBase', 'genereBaseUtilisateur').'<br />';
        echo new mmWidgetButtonGoModule('Importer le fichier yaml dans la base de parametre', 'pGestionBase', 'fillParamBaseFromYaml');
        echo '<hr>';
        echo new mmWidgetButtonGoModule('genere Yaml depuis une base existante', 'pGestionBase', 'importFromDB').'<br />';
        echo new mmWidgetButtonGoModule('Migrer depuis un fichier Yaml', 'pGestionBase', 'migrateFromYaml').'<br />';
        echo new mmWidgetButtonGoModule('Migrer depuis la base', 'pGestionBase', 'modelFromDb').'<br />';
    }

    public function executeCreateDBParam(mmRequest $request) {
        if (!MODE_INSTALL) {
            echo "<h1>Desactivée. pour activer veuillez passer en mode install</h1>";
            return true;
        }
        echo "<h1>Effacement de la base</h1>";
        Doctrine_Core::dropDatabases();
        echo "OK<h1>Creation de la base si inexistante</h1>";
        Doctrine_Core::createDatabases();
        echo "OK<h1>Generation du model</h1>";
        Doctrine_Core::generateModelsFromYaml(MM_CONFIG_DIR . '/schema_param.yml', MODELS_DIR, array('generateTableClasses' => true));
        echo "OK<h1>Generation des table de la base</h1>";
        Doctrine_Core::createTablesFromModels(MODELS_DIR);
        echo "OK<h1>Termin&eacute;</h1>";
        echo '<input type="button" value="OK" onclick="goPage(\'' . genereUrlProtege('?module=pGestionBase') . '\')" />';
    }

    public function executeDumpData(mmRequest $request) {
        echo "<h1>Sauvegarde de la base de parametre</h1>";
        try {
            Doctrine::dumpData(FIXTURE_DIR . '/params_data.yml');
            echo "<div>Termin&eacute;</div>";
        } catch (Exception $e) {
            echo "<h1>Erreur</h1>";
            echo $e->getMessage();
        }
    }

    public function executeLoadData(mmRequest $request) {
        echo "<h1>Chargement de la base de parametre</h1>";
        Doctrine::loadData(FIXTURE_DIR . '/params_data.yml');
        echo "<div>Termin&eacute;</div>";
        echo '<input type="button" value="OK" onclick="goPage(\'' . genereUrlProtege('?module=pGestionBase') . '\')" />';
    }

    public function executeGenereBaseUtilisateur(mmRequest $request) {
        /*
         * Pemet de generer le fichier yaml des fichiers utilisateurs
         */
        mmUser::flashSuperAdmin('Desactive pour le moment');
        $this->redirect(url('@module'));
        return;
        $uniqueId = md5(uniqid(microtime()) . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
        mmSession::set('migrationUniqueId', $uniqueId);

        $nomFichier = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'schema_' . $uniqueId . '.yml';

        $fichierTemp = fopen($nomFichier, 'w+');
        if (!$fichierTemp) {
            //Une erreur on genere l'erreur
            throw new mmExceptionRessource("Impossible d'ouvrir le fichier $nomFichier");
        }
        //On importe les parametres de base (le fichier schema_param.yml)
        $configInitiale = file_get_contents(MM_CONFIG_DIR . '/schema_param.yml') . "\n##Description des fichiers a partir des données utilisateurs\n";

        if (fwrite($fichierTemp, $configInitiale) === false) {
            throw new mmExceptionRessource("Erreur d'ecriture dans le fichier $nomFichier");
        }

        //On commence par recuperer la liste des tables
        $listeTables = Doctrine_Core::getTable('TableUtilisateur')->findAll();
        //Pour chaque table
        foreach ($listeTables as $tableCourante) {
            //On construit la declaration de table
            $chaineDeclarationTable = sprintf("%s:\n", $tableCourante['nom_table']);
            if (trim($tableCourante['nom_table_base'])) {
                $chaineDeclarationTable .= sprintf("  tableName: %s\n", $tableCourante['nom_table_base']);
            }
            $chaineDeclarationTable .= sprintf("  connection: %s\n", $tableCourante['emplacement']);

            //On recupere les champs de la table courante.
            $listeChamps = Doctrine_Core::getTable('ChampsTableUtilisateur')->findByNomTable($tableCourante['nom_table']); //$tableCourante->get('ChampsTableUtilisateur');
            $chaineDeclarationChamp = sprintf("  columns:\n");
            foreach ($listeChamps as $champCourant) {
                //Pour chaque champ on genere la declaration dans la base de structure

                if ($champCourant['val_def']) {
                    $valDefaut = $champCourant['val_def'];
                } else {
                    switch ($champCourant['type_champ']) {
                        case 'date':
                            $valDefaut = "'0000-00-00'";
                            break;
                        case 'time':
                            $valDefaut = "'00:00'";
                            break;
                        case 'boolean':
                        case 'integer':
                        case 'decimal':
                        case 'timestamp':
                            $valDefaut = 0;
                            break;
                        default:
                            $valDefaut = "''";
                            break;
                    }
                }
                $chaineDeclarationChamp .= sprintf("    %s:\n", $champCourant['nom_champ']);
                switch ($champCourant['type_champ']) {
                    case 'date':
                    case 'time':
                    case 'timestamp':
                    case 'blob':
                        $chaineType = sprintf("%s", $champCourant['type_champ']);
                        break;

                    default:
                        $chaineType = sprintf("%s(%s)", $champCourant['type_champ'], $champCourant['option_type_champ']);
                        break;
                }
                $chaineDeclarationChamp .= sprintf("      type: %s\n", $chaineType);
                $chaineDeclarationChamp .= sprintf("      notnull: %s\n", $champCourant['est_notnull'] ? 'true' : 'false');
                $chaineDeclarationChamp .= sprintf("      autoincrement: %s\n", $champCourant['est_autoincrement'] ? 'true' : 'false');
                $chaineDeclarationChamp .= sprintf("      primary: %s\n", $champCourant['est_primary'] ? 'true' : 'false');
                $chaineDeclarationChamp .= sprintf("      default: %s\n", $valDefaut);
//        $chaineDeclarationChamp .= sprintf("      index: %s\n", $champCourant['est_index']?'true':'false');
            }
            //On enregistre dans le fichier de travail
            if (fwrite($fichierTemp, $chaineDeclarationTable . $chaineDeclarationChamp) === false) {
                throw new mmExceptionRessource("Erreur d'ecriture dans le fichier $nomFichier");
            }
        }
        //fini avec le fichier de travail
        fclose($fichierTemp);

        //On affiche pour verifier et seulement en mode debug
        if (DEBUG) {
            echo "<h2>Fichier = $nomFichier</h2><pre>";
            readfile($nomFichier);
            echo "</pre>";
        }
        //On fait le menage dans le model avant de generer la mise a jour
        //Mise a jour à la version de base :
        $listeConnection = Doctrine_Manager::getInstance()->getConnections();
        foreach ($listeConnection as $connectionCourante) {
            try {
                mmSQL::requete('DROP TABLE migration_version', $connectionCourante);
            } catch (Exception $e) {
                echo "table version inexistante : c'est OK<br />";
            }
        }
        //on vide le repertoire de migration
        mmEmptyDirectory(MIGRATION_DIR);
        //On recupere la liste des classes decrite dans le model
        $analyseNouvelleStructure = Doctrine_Parser_Yml::load($nomFichier, 'yml');
        //maintenant on verifie pour chaque fichier classe de base si la table existe toujours dans la base de données
        $directory = new DirectoryIterator(MODELS_DIR . '/generated');
        $listeASupprimmer = array();
        foreach ($directory as $file) {
            if ($file != '.' && $file != '..') {
                $nomTravail = baseName($file, '.php');
                $nomTravail = substr($nomTravail, 4);
                if (!isset($analyseNouvelleStructure[$nomTravail])) {
                    $listeASupprimmer[] = $nomTravail;
                }
            }
        }

        //On supprime les classes qui n'existe plus
        foreach ($listeASupprimmer as $fichier) {
            unlink(MODELS_DIR . '/' . $fichier . '.php');
            unlink(MODELS_DIR . '/' . $fichier . 'Table.php');
            unlink(MODELS_DIR . '/generated/Base' . $fichier . '.php');
        }
        //On gere la generation des classes de migrations
        Doctrine_Core::generateYamlFromModels(sys_get_temp_dir() . "/structureBase_$uniqueId.yml", MODELS_DIR);
        Doctrine_Core::generateMigrationsFromDiff(MIGRATION_DIR, sys_get_temp_dir() . "/structureBase_$uniqueId.yml", $nomFichier);
        //
        User::flashSuccess('Generation des fichiers de migration OK.');
        echo '<input type="button" value="Migration de la base de données" onclick="goPage(\'' . genereUrlProtege('?module=pGestionBase&action=migrate') . '\')" />';
    }

    public function executeMigrate(mmRequest $request) {
        mmUser::flashSuperAdmin('Desactive pour le moment');
        $this->redirect(url('@module'));
        return;
        ob_end_clean();
        echo "<h1>Mise a jour de la base</h1>";
        echo "<div>TODO: genere toutes les tables dans chaque base pas bien. faut différentier les fichier de migration et etc</div><pre>";
        $listeConnection = Doctrine_Manager::getInstance()->getConnections();
        foreach ($listeConnection as $connectionCourante) {
            try {
                $migration = new Doctrine_Migration(MIGRATION_DIR, $connectionCourante);
                $migration->migrate();
            } catch (Doctrine_Migration_Exception $e) {
                echo $e->getMessage();
            }
        }
        User::flashSuccess('Migration de la base terminé.');
        echo '</pre><input type="button" value="Mise a jour du model doctrine" onclick="goPage(\'' . genereUrlProtege('?module=pGestionBase&action=model') . '\')" />';
    }

    public function executeModel(mmRequest $request) {
        mmUser::flashSuperAdmin('Desactive pour le moment');
        $this->redirect(url('@module'));
        return;

        $uniqueId = mmSession::get('migrationUniqueId', false);
        if (!$uniqueId) {
            //On a pas d'identifiant unique on interompt la migration
            throw new mmExceptionRessource("Il n'ya pas d'identifiant unique de mise a jour. Veuillez recommencer");
        }
        $cheminFichierGenere = MODELS_DIR . '/generated';

        //TODO: ajouter le nettoyage du model pousser, pour le moment on vire les fichier du repertoire $cheminFichierGenere
        mmEmptyDirectory($cheminFichierGenere);
        //Generation des classes en fonction des parametres
        $nomFichier = sys_get_temp_dir() . "/schema_$uniqueId.yml";
        echo "OK<h1>Generation du model</h1>";
        Doctrine_Core::generateModelsFromYaml($nomFichier, MODELS_DIR, array('generateTableClasses' => true));
        echo "termine";
    }

    public function executeImportFromDB(mmRequest $request) {
        $form = new mmForm();
        $form->setAction(url('@module/importFromDB'));
        //fields
        $form->addWidget(new mmWidgetText('file_name', 'model.yml'));
        $form->addWidget(new mmWidgetText('db'));
        $form->addWidget(new mmWidgetText('host', '127.0.0.1'));
        $form->addWidget(new mmWidgetText('user'));
        $form->addWidget(new mmWidgetPassword('passwd'));
        $form->addWidget(new mmWidgetButtonSubmit());
        //validator
        $form['file_name']->addValidation('notnull');
        $form['db']->addValidation('notnull');
        $form['host']->addValidation('notnull');
        $form['user']->addValidation('notnull');
        $form['passwd']->addValidation('notnull');
        
        $form->setValues($request);
        if ( ! $request->isEmpty() && $form->isValid()) {
            $db = $form->getValue('db');
            $host = $form->getValue('host');
            $user = $form->getValue('user');
            $passwd = $form->getValue('passwd');
            $fileName = $form->getValue('file_name');
            //On abandonne la connection actuelle
            $conn = Doctrine_Manager::connection();
            $manager = Doctrine_Manager::getInstance();
            $manager->closeConnection($conn);
            //On cre la nouvelle connection
            $conn = Doctrine_Manager::connection("mysql://$user:$passwd@$host/$db", "data");
            $conn->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, true);
            try {
                $path = APPLICATION_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$fileName;
                Doctrine_Core::generateYamlFromDb(APPLICATION_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$fileName, array('data'));
                echo "<h1>Import ok</h1>";
                $content = file_get_contents($path);
                echo "<pre>$content</pre>";
            }
            catch (Doctrine_Exception $e) {
                echo "<h1>Erreur lors de la creation</h1>";
                echo $e->getMessage();
                echo "<pre>".$e->getTraceAsString()."</pre>";
            }
        } else {
            echo $form->start();
            echo $form->render();
            echo $form->stop();
        }
    }
    /**
     * Cette methode prend un fichier yaml et effectue la migration de la base en fonction de celui-ci par rapport au model existant
     * @param mmRequest $request
     */
    public function executeMigrateFromYaml(mmRequest $request) {
        $form = new mmForm();
        $form->addWidget(new mmWidgetFile('ymlFile'));
        $form->addWidget(new mmWidgetButtonSubmit());
        
        $form->setValues($request);
        if ( ! $request->isEmpty() && $form->isValid()) {
            echo "<h1>Importe</h1>";
            $uniqueId = uniqid();
            $manager = Doctrine_Manager::getInstance();
            //recupération du fichier yaml
            $importYaml = APPLICATION_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'model.yml';
            //On construit le nouveau fichier yaml en mixant le fichier importer avec la base standard
            //parsing des fichier sources: le fichier de base de mihimana + le fichier uploadé
            $mihimanaDBArray = sfYaml::load(MM_CONFIG_DIR.DIRECTORY_SEPARATOR.'schema_param.yml');
            $sourceArray =  sfYaml::load($importYaml);
            //merge des tableau et ecriture du fichier temporaire
            $resultArray = array_merge($mihimanaDBArray, $sourceArray);
            $newYamlStr = sfYaml::dump($resultArray);
            
            $currentModelYaml = sys_get_temp_dir().DIRECTORY_SEPARATOR."currentModel_$uniqueId.yml";
            $newModelYaml =  sys_get_temp_dir().DIRECTORY_SEPARATOR."newModel_$uniqueId.yml";
            
            file_put_contents($newModelYaml, $newYamlStr);
            
            //récupération du yaml a partir du model existant

            Doctrine_Core::generateYamlFromModels($currentModelYaml, MODELS_DIR);
            //generation des classes de migration
            Doctrine_Core::generateMigrationsFromDiff(MIGRATION_DIR, $currentModelYaml, $newModelYaml);
            
            //On fait la migration du model
            mmEmptyDirectory(MODELS_DIR.DIRECTORY_SEPARATOR.'generated');
            Doctrine_Core::generateModelsFromYaml($newModelYaml, MODELS_DIR, array('generateTableClasses' => true));
            //On fait la migration de la base
            try {
                $migration = new Doctrine_Migration(MIGRATION_DIR);
                $migration->migrate();
            } catch (Doctrine_Migration_Exception $e) {
                echo $e->getMessage();
            }
                
            
            unlink($currentModelYaml);
            unlink($newModelYaml);
        }
        
        echo $form->start();
        echo $form->render();
        echo $form->stop();
        
        
    }
    public function executeModelFromDb(mmRequest $request) {
        //premiere chose on cre les classe de migration afin de garder un trace de la mise a jour.
        try {
            Doctrine_Core::generateMigrationsFromDb(MIGRATION_DIR);
            //on genere le model depuis la base
            Doctrine_Core::generateModelsFromDb(MODELS_DIR);
            mmUser::flashSuccess('Le model a été mis a jour');
            $this->executeIndex($request);
        }
        catch (Exception $e) {
            echo "<h1>Erreur lors de la mise a jour du model</h1>";
            echo "<div>err N° : ".$e->getCode()."</div>";
            echo "<div>".$e->getMessage()."</div>";
            echo "<div><pre>".$e->getTraceAsString()."</pre></div>";
        }
    }
    public function executeFillParamBaseFromYaml (mmRequest $request) {
        $uniqId = uniqid();
        $currentModelYaml = sys_get_temp_dir().DIRECTORY_SEPARATOR."currentModel_$uniqId.yml";
        
        Doctrine_Core::generateYamlFromModels($currentModelYaml, MODELS_DIR);
        $arrayYaml = sfYaml::load($currentModelYaml);
        
        echo "<pre>".print_r($arrayYaml, true)."</pre>";
        foreach($arrayYaml as $tableName => $tableChamps) {
            $tableUtilisateur = new TableUtilisateur();
            $tableUtilisateur['nom_table'] = $tableName;
            $tableUtilisateur['emplacement'] = $tableChamps['connection'];
            $tableUtilisateur['nom_table_base'] = $tableName;
            $tableUtilisateur->save();
            
            foreach ($tableChamps['columns'] as $nomChamp => $paramChamp) {
                $champsTableUtilisateur = new ChampsTableUtilisateur();
                $champsTableUtilisateur['nom_table'] = $tableName;
                $champsTableUtilisateur['nom_champ'] = $nomChamp;
                if (is_array($paramChamp)){
                    $typeChamp = $paramChamp['type'];
                    if (isset($paramChamp['autoincrement']) && $paramChamp['autoincrement'] == '1') $champsTableUtilisateur['est_autoincrement'] = 1;
                    if (isset($paramChamp['primary']) && $paramChamp['primary'] == '1') $champsTableUtilisateur['est_primary'] = 1;
                    if (isset($paramChamp['notnull']) && $paramChamp['notnull'] == '1') $champsTableUtilisateur['est_notnull'] = 1;
                } else {
                    $typeChamp = $paramChamp;
                }
                if (preg_match('#(\w+)\((\w*(, *\w)?)\)#', $typeChamp, $arParseType)) {
                    $champsTableUtilisateur['type_champ'] = $arParseType[1];
                    $champsTableUtilisateur['option_type_champ'] = $arParseType[2];
                } else {
                    $champsTableUtilisateur['type_champ'] = 'string';
                    $champsTableUtilisateur['option_type_champ'] = '45';
                }
                $champsTableUtilisateur->save();
            }
        }
                
    }
    
    public function executeNettoyerModel(mmRequest $request) {
        echo "<h1>Nettoyage du model</h1>";
        //on va supprimer les classes qui ne sont plus

        echo "termine";
    }

}