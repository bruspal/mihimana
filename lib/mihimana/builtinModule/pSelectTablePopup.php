<?php
/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: builtinModule
  @file : pSelectTablePopup.php
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
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pSelectTablePopup
 *
 * @author bruno
 */
class pSelectTablePopup extends mmProgCRUD {

    public function configure(mmRequest $request) {
        $this->setLayout('popup');
        $this->tableName = 'Tables';
        $this->tablesTravail = $this->recupParametre($request);
        if (!$this->tablesTravail) {
            return false;
        }

        $this->options['cols'] = array('nom' => 'Clé', 'valeur' => 'Libellé');
        $this->options['condition'] = "id_table = '$this->tablesTravail'";
        $this->options['addDelete'] = true;
    }

    public function executeIndex(mmRequest $request) {
        if (!$this->tablesTravail) {
            return false;
        }
        echo "<fieldset><legend>Edition de la table " . $this->tablesTravail . "</legend>";
        parent::executeIndex($request);
        echo "</fieldset>";
    }

    public function _executeIndex(mmRequest $request) {
        //On recupere le nom du parametre sur lequel on va travailler depuis l'url ou la session. Si y'a pas: erreur
        $tablesTravail = $this->recupParametre($request);
        if (!$tablesTravail) {
            return false;
        }
        //chargement des enregistrement + affichage
        $listeTables = Doctrine_Core::getTable('Tables')->findByIdTable($tablesTravail);
        //generation html ici
        ?>
        <fieldset>
            <legend>Edition de la table de parametres <?php echo $tablesTravail ?></legend>
            <table class="list">
                <thead>
                    <tr>
                        <th>Cl&eacute;</th>
                        <th>Libell&eacute;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listeTables as $ligneTable): ?>
                        <tr onclick="goPage('<?php echo genereUrlProtege('?module=pSelectTablePopup&action=editer&id=' . $ligneTable['id']) ?>')">
                            <td><?php echo $ligneTable['nom'] ?></td>
                            <td><?php echo $ligneTable['valeur'] ?></td>
                        </tr>
                    <?php endforeach; ?> 
                </tbody>
            </table>
        </fieldset>
        <div class="navigate">
            <?php echo new mmWidgetButtonClose() ?>
            <?php echo new mmWidgetButtonGoPage('Nouveau', genereUrlProtege('?module=' . MODULE_COURANT . '&action=nouveau')) ?>
        </div>
        <?php
    }

    public function executeNouveau(mmRequest $request, $afficherFormulaire = true) {
        $tablesTravail = $this->recupParametre($request);
        if (!$tablesTravail) {
            return false;
        }
        $tables = new Tables();
        $tables['id_table'] = $tablesTravail;
        $this->initForm($tables, true);
        if ($afficherFormulaire) {
            $this->afficheFormulaire();
        }
    }

    public function initForm(Doctrine_Record $table, $nouveau) {
        parent::initForm($table, $nouveau);
        $this->form['nom']->setLabel('Cl&eacute;');
        $this->form['valeur']->setLabel('Libell&eacute;');
        unset($this->form['id_table']);
    }

    protected function recupParametre($request) {
        $tablesTravail = $request->getParam('id', false);
        if (!$tablesTravail) {
            $tablesTravail = User::get('__idEditionTablesChoix__', false);
            if (!$tablesTravail) {
                echo mmErrorMessage('Le nom du parametre de travail est manquant');
                return false;
            }
        } else {
            User::set('__idEditionTablesChoix__', $tablesTravail);
        }
        //On verifie que le parametre est non vide
        if (trim($tablesTravail) == '') {
            echo mmErrorMessage('Le nom du parametre de travail ne peux pas etre vide');
            return false;
        }

        return $tablesTravail;
    }

}