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
  @file : pStructurePopup.php
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


require_once 'widgetsList.php';

class pStructurePopup extends mmProgCRUD {

    protected
            $typeChamp = array(
                'string' => 'Chaîne',
                'integer' => 'Entier',
                'decimal' => 'Décimal',
                'boolean' => 'Booléen',
                'clop' => 'Zone de text',
                'blob' => 'Blob binaire',
                'timestamp' => 'Timestamp',
                'time' => 'heure',
                'date' => 'Date',
                'array' => 'Tableau'
                    ),
            $ouiNon = array(0 => 'non', 1 => 'oui');

    public function configure($parametres) {
        $this->setLayout('popup');
//    $this->options['genereIndex'] = false;
        $this->tableName = 'ChampsTableUtilisateur';
    }

    public function initForm($table, $nouveau) {
        //On complete l'objet
        $nomTable = $this->parametresProgramme->getParam('table', false); //on recupere le nom de la table en parametre
        if (!$nomTable) {
            //si pas trouver on cherche dans la session
            $nomTable = mmSession::get('__structureNomTable__', false);
            if (!$nomTable) {
                throw new mmExceptionControl("Aucune table n'a été donnée au module d'edition de la structure des tables utilisateurs");
            }
        } else {
            mmSession::set('__structureNomTable__', $nomTable);
        }
        $table['nom_table'] = $nomTable;
        //On contruit le formulaire standard. On demande au papa
        parent::initForm($table, $nouveau);

        //On 'specialise' le formulaire
//    $this->form->addJavascript('reload', 'refreshParent();');
        $this->form->addWidget(new mmWidgetSelect($this->form['type_champ'], $this->typeChamp));
        $this->form->addWidget(new mmWidgetSelect($this->form['type_widget'], $GLOBALS['typeWidget']));
        $this->form->addWidget(new mmWidgetTextArea($this->form['option_type_widget'], '', array('cols' => '20', 'rows' => '10')));
        $this->form->addWidget(new mmWidgetButtonClose());
//    $this->form->addWidget(new mdWidgetButton('nouveau', 'Nouveau', array('onclick'=>sprintf("goPage('%s')",  genereUrlProtege('?module=pStructurePopup&action=nouveau')))));
        $this->form['nom_table']->disable();
    }

    public function afficheFormulaire() {
        echo '<fieldset><legend>Edition du champ ' . $this->form->getValue('nom_champ') . '</legend>';
        parent::afficheFormulaire();
//            array(
//                'nom_table', 'nom_champ', 'type_champ','option_type_champ',
//                'est_autoincrement'
//            )
//    );
        echo '</fieldset>';
    }

    public function executeIndex($parametres) {
        ?>
        <script type="text/javascript">
            refreshParent();
            window.close();
        </script>
        <?php

    }

}