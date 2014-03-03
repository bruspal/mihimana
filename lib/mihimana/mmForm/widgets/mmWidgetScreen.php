<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm/widgets
  @file : mmWidgetScreen.php
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

/**
 * widget contenant le code html d'un ecran fournis en parametre
 *
 * @author bruno
 */
class mmWidgetScreen extends mmWidget {

    protected
            $nomEcran,
            $nomVar,
            $indexForm;

    public function __construct($name, $nomEcran, $nomVar = false, $indexForm = false) {
        parent::__construct($name, '', $nomEcran);
        $this->nomEcran = $nomEcran;
        $this->nomVar = $nomVar;
        $this->indexForm = $indexForm;
    }

    //on effectue le rendu
    public function render() {
        //recuperation des variables annexes
        $variables = array();
        if ($this->containerForm) {
            $variables = & $this->containerForm->variablesExtra;
        }
        //recuperation des données vivantes
        if ($this->nomVar !== false) {
            if (strtoupper($this->nomVar == '_clone')) {
                if ($this->containerForm) {
                    $varRecord = $this->containerForm->getRecord();
                } else {
                    $varRecord = null;
                }
            } else {
                $this->nomVar = str_replace('$', '', $this->nomVar);
                if (isset($variables[$this->nomVar])) {
                    $varRecord = $variables[$this->nomVar];
                } else {
                    mmUser::flashError("Widget Ecran externe $this->nomVar: La variable fournis n'existe pas");
                    $varRecord = null;
                }
            }
        } else {
            $varRecord = null;
        }
        //insertion des variables extra
        if ($this->containerForm !== null) {
            $injectionVariables = array($this->containerForm->getToutesVariables());
//      if ($varRecord !== null)
//      {
//        $injectionVariable = array($varRecord->toArray(), $variables);
//      }
//      else
//      {
//        $injectionVariable = array($variables);
//      }
        }

        //on instancie l'ecran
        $ecran = new mmScreen($this->nomEcran, $varRecord, $injectionVariables);

        if ($this->containerForm !== null) {
            $NFContainer = $this->containerForm->getNameFormat();
            //on modifie le nomage des champs
//      if ($this->indexForm !== false)
//      {
//        $nomFormat = mdParseValeurVariables($this->indexForm, $injectionVariables[0]);
//        $nomFormat = sprintf($NFContainer.'%s', $this->nomEcran, $nomFormat);
////        $ecran->setNameFormat($nomFormat);
//      }
//      else
//      {
//        $nomFormat = sprintf($NFContainer.'[%%s]', $this->nomEcran);
////        $ecran->setNameFormat($nomFormat);
//      }
        }
        //TODO: En attendant mieux on stoqck le sous ecran dans le rendu: Ce qu'il faudra faire c'est rajouter une couche qui execute une sorte de prerender destiner a juste preparer les parametres sans effectuer le rendu
        if ($this->containerForm != null) {
            if (trim($this->indexForm) != '') {
                $this->containerForm->addForms($ecran, $this->nomEcran, mmParseVariablesValue($this->indexForm, $injectionVariables[0]));
            } else {
                $this->containerForm->addForms($ecran, $this->nomEcran);
            }
        }

        //on effectue le rendu en omettant de mettre les balise <form...></form> grace au parametre a faux
        $html = $ecran->render(false);
        if ($ecran->getRendu() == 'htm') {
            $html = preg_replace('#\s+#', ' ', $html); //on remplace les blanc par un seul espace
        }
        //return
        return $html;
    }

    public function renderPdf() {
        //recuperation des variables annexes
        $variables = array();
        if ($this->containerForm) {
            $variables = & $this->containerForm->variablesExtra;
        }
        //recuperation des données vivantes
        if ($this->nomVar !== false) {
            if (strtoupper($this->nomVar == '_clone')) {
                if ($this->containerForm) {
                    $varRecord = $this->containerForm->getRecord();
                } else {
                    $varRecord = null;
                }
            } else {
                $this->nomVar = str_replace('$', '', $this->nomVar);
                if (isset($variables[$this->nomVar])) {
                    $varRecord = $variables[$this->nomVar];
                } else {
                    mmUser::flashError("Widget Ecran externe $this->nomVar: La variable fournis n'existe pas");
                    $varRecord = null;
                }
            }
        } else {
            $varRecord = null;
        }
        //insertion des variables extra
        if ($this->containerForm !== null) {
            $injectionVariables = array($this->containerForm->getToutesVariables());
        }

        //on instancie l'ecran
        $ecran = new mmScreen($this->nomEcran, $varRecord, $injectionVariables);
        //on force le passage en mode PDF
        $ecran->setDestination('imp');

        if ($this->containerForm !== null) {
            $NFContainer = $this->containerForm->getNameFormat();
            //on modifie le nomage des champs
        }
        //TODO: En attendant mieux on stoqck le sous ecran dans le rendu: Ce qu'il faudra faire c'est rajouter une couche qui execute une sorte de prerender destiner a juste preparer les parametres sans effectuer le rendu
        if ($this->containerForm != null) {
            if (trim($this->indexForm) != '') {
                $this->containerForm->addForms($ecran, $this->nomEcran, mmParseVariablesValue($this->indexForm, $injectionVariables[0]));
            } else {
                $this->containerForm->addForms($ecran, $this->nomEcran);
            }
        }

        //on effectue le rendu en omettant de mettre les balise <form...></form> grace au parametre a faux
        $html = $ecran->render(false);
        if ($ecran->getRendu() == 'htm') {
            $html = preg_replace('#\s+#', ' ', $html); //on remplace les blanc par un seul espace
        }
        //return
        return $html;
    }

}