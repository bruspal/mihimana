<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmForm
  @file : mmForm.php
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

class mmForm extends mmObject implements ArrayAccess {

    public
            $widgetList = array(),
            $formsList = array(),
            $enctype = 'application/x-www-form-urlencoded',
            $controlList = array(); //probablement a virer
    protected
            $nameFormat = '%s',
            $record = null,
            $valid = true,
            $renderWidgetList = null,
            $listDroits = array(),
            $name = 'form',
            $new = true,
            $read,
            $edit,
            $label = '',
            $ecran = '',
            $javascripts = array(),
            $adminMenu = true,
            $method = 'post',
            $action = '',
            $id = '',
            $modified,
            $errors = array();
    protected //default options, see constructor phpdoc for details
            $options = array(
        'deep' => false,
        'auto-ng-model' => false,
        'ng-controller' => false
    );

    /**
     * Create a new form<br>
     * options is array of 'option_name'=>'value':
     * <ul>
     * <li>'deep' => (boolean) : performe an automated subform hydratation based on $record (default false). If no record defined this option will be ignored.</li>
     * <li>'auto-ng-model' => (boolean) : if true auto add ng-model attribute to all included widget (default false)</li>
     * <li>'ng-controller' => (mixed) : Add ng-controller attribute to the form header. If false do nothing (default), if true use ng-controller=form_name."Ctrl" as controller name for the form, if string use the string as ng-controller name</li>
     * </ul>
     * @param Doctrine_Record $record
     * @param array $options an array of options, see description for availlable options
     */
    public function __construct(Doctrine_Record $record = null, $options = array()) {
        $this->new = true;
        //check if exists unknown option
        if ($diffArray = array_diff_key($options, $this->options)) {
            throw new mmExceptionForm('mmForm: unknowns option ' . $diffArray[0]);
        }

        $this->options = array_merge($this->options, $options);

        //manage options
        if ($this->options['deep']) {
            $this->setSubFormFromRecord($record);
        }

        //fill in the form regarding the $record
        if ($record) {
            $this->setWidgetFromRecord($record);
            $this->new = !$this->record->exists();
        }
//    if (myUser::superAdmin() && $this->adminMenu) {
//      $this->addJavascript('admin_menu', mdTemplate::renderTemplate('jsAdmin.js', array('baseUrl'=>$_SERVER['SCRIPT_NAME'].'/Droits/', 'screen' => '')));
//    }
    }

    /**
     * magic call of $this->render()
     * @return string
     */
    public function __toString() {
        return $this->render();
    }

    /**
     * Set the form's action
     * @param type $action
     * @param type $encodeUrl @deprecated since version 1.0-ALPHA_1
     */
    public function setAction($action, $encodeUrl = true) {
        $this->action = $action;
    }

    /**
     * Return the form action
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set method 'post' (default) or 'get'
     * @param string $method
     * @throws mmExceptionForm
     */
    public function setMethod($method) {
        $method = strtolower($method);
        if ($method == 'post' || $method == 'get') {
            $this->method = $method;
        } else {
            throw new mmExceptionForm("La method pour un formulaire ne peut etre que 'post' ou 'get'");
        }
    }

    /**
     * @deprecated since version 1.0_ALPHA-1
     * @param type $adminMenu
     */
    public function setAdminMenu($adminMenu) {
        $this->adminMenu = $adminMenu;
        //On vire le script si il exist et si on desactive
        if (!$adminMenu && isset($this->javascripts['admin_menu'])) {
            unset($this->javascripts['admin_menu']);
        }
        //setup des widget
        foreach ($this->widgetList as $w) {
            $w->setAdminMenu($adminMenu);
        }
        //setup des form imbrique
        foreach ($this->formsList as $f) {
            $f->setAdminMenu($adminMenu);
        }
    }

    /**
     * Add or Replace a inner widget
     * @param mmWidget $widget mmWidget instance
     * @param mmWidget $ignoreNameFormat (default false) if set to true the widget's namr format won't be changed
     */
    public function addWidget(mmWidget &$widget, $ignoreNameFormat = false) {

        $name = $widget->getName();

        //Mise a jour auto du label
        $widget->setLabel(ucfirst(str_replace('_', ' ', $name)));
        //format de nom
        if (!$ignoreNameFormat) {
            $widget->setNameFormat(sprintf($this->nameFormat, $name));
        }

        //ajoute le formulaire courant comme formulaire contenant
        $widget->setContainer($this);

        //hook d'apres ajout
        $widget->postAddWidget();

        //ajout des parametre supplémentaire
        //ng-model
        if ($this->options['auto-ng-model']) {
            $ngModel = sprintf($this->nameFormat, $name);
            $ngModel = str_replace(array('[', ']'), array('.', ''), $ngModel);
            $widget->addAttribute('ng-model', $ngModel);
        }

        //revoir ce que ca fait
        if ($this->getId() && !$widget->isOverridden()) {
            $widget->setId($this->getId() . '_' . $widget->getId());
        }

        $this->widgetList[$name] = $widget;
    }

    /**
     * Attach a validator nammed $validatorName to the inner widget $widgetName
     * @param string $widgetName
     * @param string $validatorName
     * @param array $params validator's parameters
     * @throws mmExceptionForm
     */
    public function addValidator($widgetName, $validatorName, $params = array()) {
        if (isset($this->widgetList[$widgetName])) {
            $this->widgetList[$widgetName]->addValidator($validatorName, $params);
        } else {
            throw new mmExceptionForm("Le widget $widgetName n'existe pas.");
        }
    }

    /**
     * Add a subform
     * @param mixed $subForm can be a mmForm instance or an array of mmForm instances
     * @param type $includeName
     * @param mixed $rowIndex the row index gave to the included form, mainly used when an array of subForm is used this parameter can be used for hacking toi form subform name format
     * @throws mmExceptionForm
     */
    public function addForms($subForm, $includeName, $rowIndex = false) {
        if ($subForm instanceof mmForm) {
            if ($includeName) {
                $subForm->setName($this->name . '[' . $includeName . ']');
                if ($rowIndex === false) {
                    $subForm->setNameFormat(sprintf('%s[%s][%%s]', $this->name, $includeName));
                    $baseId = $includeName;
                } else {
                    $subForm->setNameFormat(sprintf('%s[%s][%s][%%s]', $this->name, $includeName, $rowIndex));
                    $baseId = $includeName . '_' . $rowIndex;
                }
                //$subForm->setAdminMenu(false);
                foreach ($subForm->widgetList as $wn => $w) {
                    $w->setId($baseId . '_' . $w->getId());
                    //$w->setAdminMenu(false);
                }
                if ($rowIndex !== false) {
                    $this->formsList[$includeName][$rowIndex] = $subForm;
                } else {
                    $this->formsList[$includeName] = $subForm;
                }
            }
//      $this->formsList[$includeName] = $subForm;
        } else {
            if (is_array($subForm)) {
                if (empty($includeName)) {
                    throw new mmExceptionDev('Formulaire imbriqu&eacute; : Dans le cas d\'imbrication de tableau de formulaire, $includeName doit etre fournis');
                }
                //on a fournis un tableau de formulaire
                foreach ($subForm as $k => $sf) {
                    if (!$sf instanceof mmForm) {
                        throw new mmExceptionDev('Formulaire imbriqu&eacute; : le tableau de formulaire imbrique contien une donn&eacute;e de type non mdForm');
                    }
                    $sf->setName("{$this->name}[$includeName][$k]");
                    $sf->setNameFormat(sprintf('%s[%s][%s][%%s]', $this->name, $includeName, $k));
                    //$sf->setAdminMenu(false);
                    foreach ($sf->widgetList as $wn => $w) {
                        $w->setId($includeName . '_' . $k . '_' . $w->getId());
                    }
                }
                $this->formsList[$includeName] = $subForm;
            } else {
                throw new mmExceptionForm('Formulaire imbriqu&eacute; : seul un mdForm ou un tableau de mdForm peut etre imbriqu&eacute;');
            }
        }
    }

    /**
     * Add or replace widgets in form
     * @param array $widgets array of widgets
     */
    public function setWidgets(array $widgets) {
        foreach ($widgets as $widget) {
            $this->addWidget($widget);
        }
    }

    /**
     * Setup form's widgets from a Doctrine_Record
     * @param Doctrine_Record $record
     * @param type $nameFormat use the %s formated string such as 'foobar[%s]' instead of default name format. @see setNameFormat for details.
     * @return boolean true if everything was ok.
     */
    public function setWidgetFromRecord(Doctrine_Record $record, $nameFormat = '') {
        // variabled utiles
        $ouiNon = array('0' => 'Non', '1' => 'Oui');

        //Initialisation de l'objet
        $this->valid = true;
        $this->name = $record->getTable()->getTableName();
        $this->id = $this->name;
        $this->screen = $this->name;
        if (!$nameFormat) {
            $this->nameFormat = sprintf('%s[%%s]', $this->name);
        } else {
            $this->nameFormat = $nameFormat;
        }

        //Pour chaque formulaire
        $fields = $record->getTable()->getColumns();

        foreach ($fields as $fieldName => $field) {
            //on defini le type de widget en fonction des infos de la base

            if (isset($field['primary']) && $field['primary']) {
                //cle primaire
                $widget = new mmWidgetHidden($fieldName);
            } else {
                $widget = $this->creerWidgetDepuisTypeChamp($fieldName, $field);
            }

            $widget->setNameFormat($this->nameFormat);
            $widget->setFromDb(true);
            try {
                $widget->setDbValue($record[$fieldName]);
            } catch (mmExceptionWidget $e) {
                $this->valide = false;
            }
            $widget->setLabel(ucfirst(str_replace('_', ' ', $fieldName)));

            //Validators
            //for primary key of existing records add the validator
            if (isset($field['primary']) && $field['primary'] && $record->exists()) {
                $widget->addValidator('notnull');
            }

            if (isset($field['length']) && $widget instanceof mmWidgetText) {
                $widget->addValidator('length_max', $field['length']);
            }
            if (isset($field['notnull']) && $field['notnull']) {
                $widget->addValidator('notnull', $field['notnull']);
//        $widget->setLabel('<strong>*</strong> '.$widget->getLabel());
            }
            if (isset($field['default'])) {
                $widget->setDefault($field['default']);
            }

            //Affectation du widget
//      $this->widgetList[$fieldName] = $widget;
            $this->addWidget($widget);

            if ($this->options['auto-ng-model']) {
                $widget->addAttribute('ng-init', $widget->getAttribute('ng-model')."='".$widget->getValue()."'");
            }

        }
        $this->record = $record;
        return $this->valid;
    }

    /**
     * Create forms and its related nested form from a Doctrine_Record
     * @param type Doctrine_Record $record
     * @return type
     */
    public function setSubFormFromRecord(Doctrine_Record $record) {
        $arrayRelation = $record->getReferences();
        foreach ($arrayRelation as $relationName => $relation) {
            $dataRelation = $relation['data'];
            if (is_array($dataRelation)) {
                $arraySubform = array();
                foreach ($dataRelation as $subRecordIdx => $subRecord) {
                    $subForm = new mmForm($subRecord, $this->options);
                    $arraySubform[] = $subForm;
                }
                $this->addForms($arraySub, $relationName);
            } else {
                $subForm = new mmForm($dataRelation, $this->options);
                $this->addForms($subForm, $relationName);
            }
        }
    }

    /**
     * Widget creation based on the field description
     * @param string $fieldName name of futur widget
     * @param array $field database field description
     * @return mmWidget created widget
     */
    protected function creerWidgetDepuisTypeChamp($fieldName, $field) {
        // variables utiles
        $ouiNon = array('0' => 'Non', '1' => 'Oui');
        $widgetType = $field['type'];

        switch ($widgetType) {
            case 'string':
                if ($field['length'] > 255) {
                    $widget = new mmWidgetTextArea($fieldName);
                } else {
                    $widget = new mmWidgetText($fieldName);
                    $widget->addAttribute('size', $field['length']);
                }
                break;
            case 'integer':
                $widget = new mmWidgetInteger($fieldName);
                if (isset($field['length'])) {
                    $length = $field['length'];
                } else {
                    $length = 10;
                }
                $widget->addAttribute('size', $length);
                break;
            case 'decimal':
            case 'float':
                $widget = new mmWidgetFloat($fieldName);
                if (isset($field['length'])) {
                    $length = $field['length'];
                } else {
                    $length = 10;
                }
                $widget->addAttribute('size', $length);
                break;
            case 'date':
                $widget = new mmWidgetDate($fieldName);
                break;
            case 'timestamp':
                $widget = new mmWidgetTimestamp($fieldName);
                break;
            case 'time':
                $widget = new mmWidgetTime($fieldName);
                break;
            case 'blob':
            case 'clob':
                $widget = new mmWidgetTextArea($fieldName);
                break;
            case 'boolean':
                $widget = new mmWidgetSelect($fieldName, $ouiNon);
                break;
            case 'array':
                $widget = new mmWidgetList($fieldName);
                break;
            default:
                //la c'est pas normal on flash l'admin et on continu en considerant le widget comme un champ text
                //Widget inconnu ou pas encore codé
                $widget = new mmWidgetText($fieldName);
                mmUser::flashSuperAdmin("$fieldName le type {$field['type']} n'est pas reconnus");
                break;
        }

        return $widget;
    }

    /*
     * Methodes d'operation sur les widget
     */

    /**
     * Set the internal DB value of the object
     * Met a jour l'objet avec les valeurs fournis en parametre au format de la base de donnees
     * ignore les champs manquant et supplementaires
     *
     * @param array $values
     */
    public function setDbValues($values = array()) {
        $this->valid = true;
        foreach ($values as $name => $value) {
            if (isset($this->widgetList[$name]) && $this->widgetList[$name]->getFromDb()) {
                try {
                    $this->widgetList[$name]->setDbValue($value);
                } catch (mmExceptionWidget $e) {
                    $this->valid = false;
                }
            }
        }
        return $this->valid;
    }

    /**
     * set values to the form, $values is a associative array 'field_name' => 'value'. Validators are checked during the process.<br>
     * If an error occure, the valide flad will be set to false.
     * @param array $values
     * @param mixed $compulsory true: all fields but mmWidgetButton,... instances are compulsory<br>otherwise an array of compulsory fields. If ommited there is no fields compulsory
     * @return boolean true if all fields are validated false otherwise
     */
    public function setValues($values = array(), $compulsory = false) {
        $this->valid = true;
        /*
         * Because of the behaviour of foreach regarding arrayAccess implementation, mmVarHolder is internally turned to an array (BUG #34445 : https://bugs.php.net/bug.php?id=34445)
         */
        if ($values instanceof mmVarHolder) {
            $_values = $values->toArray();
        } else {
            $_values = & $values;
        }
        //Check if if compulsory fields are presents
        if ($compulsory !== false) {
            if ($compulsory === true) {
                foreach ($this->widgetList as $wName => $widgetInstance) {
                    if ($widgetInstance instanceof mmWidgetButton)
                        continue;
                    if (!isset($_values[$wName])) {
                        $this->valid = false;
                        $this->addError('missing field ' . $wName);
                    }
                }
            } else {
                $compulsory = (array) $compulsory;
                foreach ($compulsory as $cField) {
                    if (!isset($_values[$cField])) {
                        $this->valid = false;
                        $this->addError('missing field ' . $cFields);
                    }
                }
            }
        }
        // do affectation to all fields provided in values
        foreach ($_values as $name => $value) {
            if (isset($this->widgetList[$name])) {
//                try {
                    if (function_exists('mmFormBeforeSetValue')) {
                        if (mmFormBeforeSetValue()) {
                            $this->setValue($name, $value);
//                            $this->widgetList[$name]->setValue($value);
                        }
                    } else {
                        $this->setValue($name, $value);
//                        $this->widgetList[$name]->setValue($value);
                    }
                    //On verifie les droits
//          if (myUser::superAdmin() || isset($this->listDroits[$name]['edit']) && $this->listDroits[$name]['edit']) {
//                    if ($value == "O") {
//                        $rien = $value;
//                    }
//          }

//                //                } catch (mmExceptionWidget $e) {
//                    $this->valid = false;
//                }
            } else {
                //est-ce pour le formulaire imprique ?
                if (is_array($value) && isset($this->formsList[$name])) {
                    if ($this->formsList[$name] instanceof mmForm) {
                        //C'est une formulaire
                        if (!$this->formsList[$name]->setValues($value)) {
                            $this->valid = false;
                        }
                    } else {
                        if (is_array($this->formsList[$name])) {
                            foreach ($this->formsList[$name] as $k => $sf) {
                                if ($sf instanceof mmForm) {
                                    if (isset($value[$k])) {
                                        if (!$sf->setValues($value[$k])) {
                                            $this->valid = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->valid;
    }

    /**
     * Set the $value of the $fieldname widget. Set the form in invalid state if something went wrong.
     * @param type $fieldName
     * @param type $value
     */
    public function setValue($fieldName, $value) {
        if (isset($this->widgetList[$fieldName])) {
            try {

                if ($this->options['auto-ng-model']) {
                    $this->widgetList[$fieldName]->addAttribute('ng-init', $this->widgetList[$fieldName]->getAttribute('ng-model')."='".$value."'");
                }
                $this->widgetList[$fieldName]->setValue($value);

            } catch (mmExceptionWidget $e) {
                $this->valid = false;
            }
        } else {
            throw new mmExceptionForm("The widget $fieldName doesn't exists");
        }
    }

    /**
     * return an array of widget values
     * @return array
     */
    public function getValues() {
        $result = array();
        foreach ($this->widgetList as $wn => $widget) {
            $result[$wn] = $widget->getValue();
        }
        return $result;
    }

    /**
     * Get the widget value of the widget $fieldName
     * @param type $fieldName
     * @return type
     */
    public function getValue($fieldName) {
        return $this->widgetList[$fieldName]->getValue();
    }

    /**
     * set the form name
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
        //On met a jour le nameFormat
//    $this->nameFormat = sprintf()
    }

    /**
     * get the form's name
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Return the table name or empty string if there is no associed table
     * @return type
     */
    public function getTableName() {
        if ($this->record === null) {
            return '';
        } else {
            return $this->record->getTable()->getTableName();
        }
    }
    /**
     * set the form html id tag for the form
     * @param string $id
     */
    public function setId($id = '') {
        $this->id = $id;
    }

    /**
     * Get the ihtml id tag
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    //TODO: Voir ce que ca fait ici
    /**
     * set screen. @todo probably deprecated
     * @param type $screen
     * @return type
     */
    public function setScreen($screen) {
        $this->screen = $screen;
    }

    //TODO: revoir ce que ca fait ici
    /**
     * get screen. @todo probably deprecated
     * @param type $screen
     * @return type
     */
    public function getScreen() {
        return $this->screen;
    }

    //TODO: voir si c'est a virer
    /**
     * set label. @todo probably deprecated
     * @param type $label
     * @return type
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    //TODO: voir si on doit virer ca
    /**
     * get label. @todo probably deprecated
     * @param type $label
     * @return type
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Met a jour l'ensemble des label des widget.
     * @param type $labels tableay ('nom widget'=>'label')
     */
    public function setLabels($labels) {
        foreach ($labels as $wn => $label) {
            if (isset($this->widgetList[$wn])) {
                $this->widgetList[$wn]->setLabel($label);
            }
        }
    }
    /**
     * set the name format of inner widgets, name format must contains '%s' in with the widget name takes place
     * @param string $format
     */
    public function setNameFormat($format) {
        $this->nameFormat = $format;
        //On applique le nouveau nameFormat a tous les widgetAssocie
        if ($this->options['auto-ng-model']) {
            foreach ($this->widgetList as $wn => $w) {
                $w->setNameFormat($this->nameFormat);
                $ngModel = sprintf($this->nameFormat, $wn);
                $ngModel = str_replace(array('[', ']'), array('.', ''), $ngModel);
                $w->addAttribute('ng-model', $ngModel);
            }
        } else {
            foreach ($this->widgetList as $wn => $w) {
                $w->setNameFormat($this->nameFormat);
            }
        }
        /*
          //on traite tous les cas des sous formulaire
          foreach ($this->formsList as $fName => $subForm) {
          if ($subForm instanceof mmForm) {
          echo "<h1>subform $fName ".$subForm->getNameFormat()."</h1>";
          } else {
          if (is_array($subForm)) {
          foreach ($subForm as $forRow => $subFormRow) {
          if ($subFormRow instanceof mmForm) {
          echo "<h1>subform[$forRow] $fName ".$subFormRow->getNameFormat()."</h1>";
          } else {
          throw new mmExceptionDev('array de sous forme contenant un truc pas legal');
          }
          }
          } else {
          throw new mmExceptionDev('pas mmForm et pas array');
          }
          }

          }
         */
    }

    /**
     * get the form's nameFormat
     * @return type
     */
    public function getNameFormat() {
        return $this->nameFormat;
    }

    /**
     * display form
     *
     * @param array $fieldList array of string. enumerate which field will be displayed, if ommited or empty it will display every widgets<br>
     * @param array $subFormsSetting parameters array to defined how to render subforms:
     * <table>
     * <tr><th colspan="2">subform setup</th></tr>
     * <tr><td>subform_name</td>
     * </table>
     * : tableau de paramettrage de rendu des sous-formulaires array(nom_interne =><br />
     * array('fieldList'=>comme $fieldList, 'renderType'=> type de rendu, 'label'=> label a afficher dans le cas du renderType='fieldset'))<br />
     * type de rendu:<ul><li>table (default): rend le formulaire comme une table html</li><li>fieldset: rend le formulaire comme avec renderFieldset</li><li>row: rend le formulaire dans une table horizontale</li></ul>
     * @return type
     */
    public function render($fieldList = null, $subFormsSetting = false) {
        $fieldList = $this->generateFieldsList($fieldList);
        $result = '';
        foreach ($fieldList as $wn => $widget) {
            if (($wn == 'id' && $widget instanceof mmWidgetHidden) || $widget instanceof mmWidgetHidden) { //TODO: ameliorer le filtre pour rendre par automatique les champs index invisible
                $result .= '<tr style="display: none;"><td colspan="2">' . $widget->render() . '</td></tr>';
            } else {
                if (!($widget instanceof mmWidgetButton)) {
                    $result .= $widget->renderRow();
                }
            }
        }
        //rendu des formulaires imbrique
        $strSubForms = $this->renderSubForms($subFormsSetting);
        if ($strSubForms) {
            $result .= sprintf('<tr><td colspan="2">%s</td></tr>', $strSubForms);
        }
        //bouton et table finale
        $buttons = $this->renderButtons();
        if (trim($buttons) != '') {
            $result .= '<div class="navigation">' . $buttons . '</div>';
        }

        if (trim($result) != '') {
            $result = '<table class="form" id="' . $this->name . '_form_id">' . $result . '</table>';
        }
        //on ajoute le rendu des erreurs global
        $result .= $this->renderErrors();
        //On ajoute les balise <form> si l'action a ete definie
        if ($this->action) {
            $result = $this->start() . $result . $this->stop();
        }
        $result .= $this->renderJavascript($fieldList);
        return $result;
    }

    /**
     * return the array of errors stored in the form instance, if deep set to false (default) it will return erros only for the current form. if set tp true, it will also return the erros of sub forms.
     * @param type $deep
     * @return type
     */
    public function getErrors($deep = false) {
        $result = array();
        if ($deep) {
            foreach ($this->widgetList as $wName => $wInstance) {
                if (!$wInstance->isValid())
                    $result['widgets'][$wName] = $wInstance->getErrors();
            }
            $result['form'] = $this->errors;
        } else {
            return $this->errors;
        }
    }

    /**
     * start form
     * @param array $extraAttributes array of extra attributes added to the form header
     * @return string
     */
    public function start($extraAttributes = array()) {
        $strExtraAttr = '';
        foreach ($extraAttributes as $aName => $aValue) {
            $strExtraAttr .= "$aName=\"$aValue\" ";
        }
        return sprintf('<form action="%s" method="%s" %s enctype="%s" %s>', $this->action, $this->method, $this->id == '' ? '' : 'id="' . $this->id . '"', $this->enctype, $strExtraAttr);
    }

    /**
     * return the inners attributes of form or any widget within the form. if $widgetName is ommited the form's attributes will be returned<br>
     * otherwise $widgetName widget's attributes are returned
     * @param string $widgetName
     * @return string
     */
    public function useAttrs($widgetName = false) {
        if ($widgetName) {
            if (isset($this->widgetList[$widgetName])) {
                return $this->widgetList[$widgetName]->useAttrs();
            } else {
                throw new mmExceptionForm("le widget $widgetName n'existe pas");
            }
        } else {
            $strExtraAttr = '';
            if ($this->options['ng-controller']) { // if ng-controller must be added to the form header
                if ($this->options['ng-controller'] === true) {
                    $strExtraAttr = ' ng-controller="' . $this->name . '"';
                } elseif (is_string($this->options['ng-controller'])) {
                    $strExtraAttr = ' ng-controller="' . $this->options['ng-controller'] . '"';
                } else {
                    throw new mmExceptionForm("Tentative d'attacher un ng-controller avec autre chose qu'un boolean ou une chaine");
                }
            }
            return sprintf('action="%s" method="%s" %s enctype="%s"', $this->action, $this->method, $this->id == '' ? '' : 'id="' . $this->id . '"', $this->enctype);
        }
    }

    /**
     * echoing the inners attributes of form or any widget within the form. if $widgetName is ommited the form's attributes will be returned<br>
     * otherwise $widgetName widget's attributes are returned
     * @param string $widgetName
     * @return string
     */
    public function renderAttrs($widgetName = false) {
        echo $this->useAttrs($widgetName);
    }

    /**
     * close the form
     * @return string
     */
    public function stop() {
        return '</form>';
    }

    /**
     * render form as a html table.
     * @param array $fieldList list of rendered widgets, if ommitted render all widgets
     * @param type $subFormsSetting  setting for subform's rendering @see render
     * @return string
     */
    public function renderRow($fieldList = null, $subFormsSetting = null) {
        $fieldList = $this->generateFieldsList($fieldList);
        $result = '';
        $head = '';
        $nbCols = 0;
        foreach ($fieldList as $wn => $widget) {
            $strWidget = $widget->render();
            if ($strWidget) { //on obtiens le widget (NOTA: si = '' c'est qu'on a pas le droit de voir/editer le champs
                if ($widget instanceof mmWidgetHidden) {
                    $result .= $strWidget;
                } else {
                    $result .= "<td>$strWidget</td>";
                    $head .= '<th>' . $widget->getLabel() . '</th>';
                }
                $nbCols++;
            }
        }
        //rendu des formulaires imbrique
        $strSubForms = $this->renderSubForms($subFormsSetting);
        if ($strSubForms) {
            $strSubForms .= sprintf('<tr><td colspan="%d">%s</td></tr>', $nbCols, $strSubForms);
        }
        if (trim($result) == '')
            return '';
        return sprintf('<table><thead><tr>%s</tr></thead><tbody><tr>%s</tr>%s</tbody></table>', $head, $result, $strSubForms) . $this->renderJavascript($fieldList);
    }

    /**
     * render the form in a fieldset container
     * @param string $legend fieldset's legend
     * @param array $fieldList list of redenred widgets, if ommited all widgets will be rendered
     * @param array $subFormsSetting subforms setting @see render
     * @return string
     */
    public function renderFieldset($legend = '', $fieldList = null, $subFormsSetting = false) {
        if ($legend == '') {
            $legend = $this->label;
        }
        $result = $this->render($fieldList, $subFormsSetting);
        if ($result) {
            return sprintf('<fieldset><legend>%s</legend>%s</fieldset>', $legend, $result);
        }
        return '';
    }

    /**
     * rendu des formulaires imbriques
     * @param type $subFormsSetting
     */
    public function renderSubForms($subFormsSetting = false) {
        if ($subFormsSetting === null) // if strictly null no render
            return'';

        $result = '';
        //no subform settings ? do render simplier
        if ($subFormsSetting === false) {
            foreach ($this->formsList as $formName => $form) {
                if ($form instanceof mmForm) { //le formulaire imbriqué est une instance de mmForm ? on fait un rendu standard
                    $result .= $form->renderFieldset($formName);
                } else {
                    if (is_array($form)) {
                        foreach ($form as $formRow) {
                            if (!$formRow instanceof mmForm) {
                                throw new mmExceptionDev('le tableau de formulaire impriqué ne peux contenir que des instance de mmForm');
                            }
                            $result .= $formRow->renderRow();
                        }
                    } else {
                        throw new mmExceptionDev('le formulaire imbriqué n\'est ni un tableau de mmForm ni un mmForm');
                    }
                }
            }
        } else {
            // rendu des formulaires imbrique
            foreach ($this->formsList as $form) {
                $internalName = $form->getName();
                if (isset($subFormsSetting[$internalName])) {
                    $params = $subFormsSetting[$internalName];
                    $subFieldList = isset($params['fieldList']) ? $params['fieldList'] : null;
                    if (isset($params['renderType'])) {
                        switch ($params['renderType']) {
                            case 'table':
                                $result .= $form->render($subFieldList);
                                break;
                            case 'fieldset':
                                $label = isset($params['label']) ? $params['label'] : '';
                                $result .= $form->renderFieldset($label, $subFieldList);
                                break;
                            case 'row':
                                $result .= $form->renderRow($subFieldList);
                                break;
                            default:
                                break;
                        }
                    } else {
                        $result .= $form->render($subFieldList);
                    }
                } else {
                    $result .= $form->render();
                }
            }
        }
        return $result;
    }

    /**
     * Genere le javascript associe au formulaire (controle et condition associe au widget et globale au formulaire). Genere aussi le script de gestion des droits en mode admin.
     *
     * @param array() $fieldList Liste des widget pour lesquel les script seront genere, null pour tous les widget
     * @param bool $inDocumentReady Inclus les script dans un $(document).ready() jQuery
     * @param bool $inScript Inclus les script entre <script></script>
     * @param bool $renderWidgetsScript a true genere les scripts associe au forme, a false ne genere pas ces scripts.
     * @return string
     */
    public function renderJavascript($fieldList = null, $inDocumentReady = true, $inScript = true, $renderWidgetsScript = true) {
        if ($fieldList === null) {
            $fieldList = $this->widgetList;
        }
        $result = '';
        $head = '';
        $foot = '';
        $widgetScript = '';
        if ($inScript) {
            $head .= "\n" . '<script type="text/javascript">' . "\n";
            $foot = "\n</script>\n";
        }
        if ($inDocumentReady) {
            $head .= "$(document).ready(function(){\n";
            $foot = '});' . $foot;
        }
        foreach ($this->javascripts as $sName => $script) {
            if (!$script['rendered']) {
                $result .= $script['script'];
                $this->javascripts[$sName]['rendered'] = true;
            }
        }
        if ($renderWidgetsScript) {
            foreach ($fieldList as $w) {
                $widgetScript .= $w->renderJavascript();
            }
        }
        if ($result . $widgetScript == '')
            return '';
        $result = "$result\n$widgetScript";
        if (!DEBUG) { // si on est pas en mode debug on vire tous les espace blanc saut de ligne et etc
            $result = preg_replace('/\s/', ' ', $result);
        }
        return $head . $result . $foot;
    }

    /**
     * Genere le code HTML associé aux bouton stocké dans le formulaire
     * @param type $fieldList liste des boutons a afficher, si omis: tous les bouttons du formulaire
     * @return type
     */
    public function renderButtons($fieldList = null) {
        $result = '';
        $fieldList = $this->generateFieldsList($fieldList);
        foreach ($fieldList as $widget) {
            if (is_a($widget, 'mmWidgetButton')) {
                $result .= $widget->render();
            }
        }
        return $result;
    }

    public function renderErrors($bloc = 'div', $line = 'p') {
        if (count($this->errors) > 0) { //there is errors
            $errorsLine = implode("</$line><$line>", $this->errors);
            return "<$bloc id=\"mmFormError_{$this->getId()}\" class=\"mmFormError\"><$line>$errorsLine</$line></$bloc>";
        } else {
            return '';
        }
    }

    /**
     * Render all widget's error
     */
    public function renderWidgetErrors() {
        $result = '<table class="errors">';
        //widgets
        foreach ($this->widgetList as $wn => $widget) {
            if (!$widget->isValid()) {
                $errors = $widget->getErrors();
                foreach ($errors as $ligne) {
                    $result.="<tr><th>$wn</th><td>$ligne</td></tr>";
                }
            }
        }
        //embeded forms
        foreach ($this->formsList as $fn => $form) {
            if ($form instanceof mmForm) {
                $result .= '<tr><th colspan="2">Subform : ' . $fn . '</th></tr>';
                $result .= '<tr><td colspan="2">' . $form->renderWidgetErrors() . '</td></tr>';
            } else {
                if (is_array($form)) {
                    foreach ($form as $afn => $aform) {
                        if ($aform instanceof mmForm) {
                            $result .= '<tr><th colspan="2">Subform : ' . $afn . '</th></tr>';
                            $result .= '<tr><td colspan="2">' . $aform->renderWidgetErrors() . '</td></tr>';
                        } else {
                            throw new mmExceptionDev('le tableau de formulaire impriqué ne peux contenir que des instance de mmForm');
                        }
                    }
                } else {
                    throw new mmExceptionDev('le formulaire imbriqué n\'est ni un tableau de mmForm ni un mmForm');
                }
            }
        }
        $result .= '</table>';
        return $result;
    }

    public function addJavascript($name, $script) {
        $this->javascripts[$name] = array('script' => $script, 'rendered' => false);
    }

    public function getJavascript($name) {
        return $this->javascripts[$name];
    }

    public function getJavascripts() {
        return $this->javascripts;
    }

    /**
     * Call a widgetMethod for every widgets of the form
     * @param string $methodName method to execute
     * @param array $params method parameters as array
     * @param boolean $deep if true apply also for included forms
     * @throws mmExceptionForm
     */
    public function foreachWidget($methodName, $params = array(), $deep = false) {
        foreach ($this->widgetList as &$widget) {
            call_user_function_array(array($widget, $methodName), $params);
        }
        if ($deep) {
            foreach ($this->formsList as $form) {
                if ($form instanceof mmForm) {
                    $form->foreachWidget($methodName, $params, $deep);
                } elseif (is_array($form)) {
                    foreach ($form as $subForm) {
                        if ($subForm instanceof mmForm) {
                            $subForm->foreachWidget($methodName, $params, $deep);
                        } else {
                            throw new mmExceptionForm('Formulaire imbriqué non tableau non mmForm');
                        }
                    }
                } else {
                    throw new mmExceptionForm('Formulaire imbriqué non tableau non mmForm');
                }
            }
        }
    }

    /**
     * Genere la list des champs rendu lors de l'affichage du formulaire
     */
    protected function generateFieldsList($fieldList) {
        if ($fieldList === null)
            return $this->widgetList;
        $result = array();
        foreach ($fieldList as $field) {
            if (isset($this->widgetList[$field])) {
                $result[$field] = $this->widgetList[$field];
            } else {
                throw new mmExceptionDev(sprintf('%s::%s le widget %s n\'existe pas', __CLASS__, __METHOD__, $field));
            }
        }
        //On inclus l'id si absent
        //TODO: mieux gere le 'id' en verifiant le type de champs dans la base, si c'est un index ba on l'ajoute
//    if ( ! isset($result['id']) && isset($this->widgetList['id'])) {
//      $result['id'] = $this->widgetList['id'];
//    }

        return $result;
    }

    public function isValid() {
        return $this->valid;
    }

    public function isNew() {
        return $this->new;
    }

    /**
     *
     * @return type
     * @throws mmExceptionDev
     */
    public function updateRecord() {
        if ($this->record) {
            // on a un enregistrement lié on sauve
//      return null;
//    }
//    else {
            $this->modified = false;
            $dbFields = $this->record->getTable()->getFieldNames();
            foreach ($this->widgetList as $wName => $widget) {
                if ($widget->getFromDb() && in_array($wName, $dbFields) && $widget->isEditable()) {
                    $orig = $this->record[$wName];
                    $nv = $widget->getDbValue();
                    //Le champs viens de la base de donnees
                    if ($this->record[$wName] != $widget->getDbValue()) {
                        $this->record->set($wName, $widget->getDbValue());
                        $this->modified = true;
                    }
                }
            }
        }
        //Update des imbrique
        foreach ($this->formsList as $form) {
            if ($form instanceof mmForm) {
                $aForm = array($form);
            } else {
                if (is_array($form)) {
                    $aForm = $form;
                } else {
                    throw new mmExceptionDev('mdForm::UpdateRecord: les formulaires imbrique doivent etre soit de type mdForm soit de type tableau');
                }
            }
            //update des formulaires
            foreach ($aForm as $sf) {
                $sf->updateRecord();
            }
        }
        return $this->record;
    }

    public function save(Doctrine_Connection $conn = null) {
        if ($this->record) {
            //TODO hooks avant save
            if ( ! $this->beforeUpdateSave()) {
                return null; //save canceled
            }
            //En enregistrement existe pour ce formulaire, on fais un enregistrement
            if ($this->valid && $this->updateRecord()) {
                $this->beforeSave();
                if ($this->record->isModified(true)) {
                    //si les données ont été changées dans l'enregistrement ou un de ses enregistrement lié, on enregistre
                    $this->record->save($conn);
                    $this->afterSave();
                }
                $this->new = !$this->record->exists();
            }
        }

//        //Le formulaire n'as pas d'enregistrement associé mais peut etre que ses eventuel sous formulaire en ont.
//        //Dans ce cas la on effectue une sauvegarde
//        foreach ($this->formsList as $form) {
//            if ($form instanceof mmForm) {
//                $form->save($conn);
//            } else {
//                if (is_array($form)) {
//                    foreach ($form as $sf) {
//                        $sf->save();
//                    }
//                } else {
//                    throw new mmExceptionDev('mdForm::save: Formulaire imbrique doit etre du type mdForm ou array');
//                }
//            }
//        }

        return $this->record;
    }
    /**
     * hooks methods before record updated with data in form. If return false save is canceled
     * @return boolean
     */
    public function beforeUpdateSave() {
        return true;
    }

    /**
     * Action to do after record updated with forms data and before saving. called only if form is valid (i.e. no errors)
     */
    public function beforeSave() {

    }

    /**
     * Action to do after records has been saved
     */
    public function afterSave() {

    }
    /**
     * retourne l'objet doctrine stocker dans le formulaire avec les donnees en cours
     * @return type
     */
    public function getRecord() {
        return $this->record;
    }

    /**
     * Return the record updated with the form values
     * @return type
     */
    public function getUpdatedRecord() {
        return $this->updateRecord();
    }

    /*
     * Gestion des droits
     */
    /**
     * @deprecated methods perhaps in an overided classe
     */
    public function loadDroits($screen = '') {
        return true;

        if ($screen) {
            $this->screen = $screen;
        }
        //menu admin script
        //TODO: faudra voir a ameliorer renderTemplate pour pourvoir l'utiliser dqns le rederJavascript afin d'avoir un rendu dynamique en fonction de l'etat de l'objet au moment du rendu
        if (myUser::superAdmin() && $this->adminMenu) {
            $this->addJavascript('admin_menu', mmTemplate::renderTemplate('jsAdmin.js', array('baseUrl' => $_SERVER['SCRIPT_NAME'] . '/Droits/', 'screen' => $this->screen)));
        }
        //s'applique si on est pas super admin
        if (!myUser::superAdmin()) {
            $droits = Doctrine_Core::getTable('DroitsEcran')->findByEcran($this->screen);
            //On applique les droits
            foreach ($droits as $droit) {
                //extraction
                if ($this->new) {
                    $edit = explode(',', $droit['creer']);
                } else {
                    $edit = explode(',', $droit['editer']);
                }
                $view = explode(',', $droit['voir']);
                //tableau de reference
//        $this->listDroits[$droit['widget']]['view'] = in_array(myUser::get()->getGroupeNameTest(), $view);
//        $this->listDroits[$droit['widget']]['edit'] = in_array(myUser::get()->getGroupeNameTest(), $edit);
                $this->listDroits[$droit['widget']]['view'] = myUser::inGroups($view);
                $this->listDroits[$droit['widget']]['edit'] = myUser::inGroups($edit);
                //mise en place de l'aide en ligne
                if (isset($this->widgetList[$droit['widget']])) {
                    $this->widgetList[$droit['widget']]->setHelp($droit['aide']);
                }
            }
            //on met a jour les widget
            foreach ($this->widgetList as $wName => $widget) {
                if (isset($this->listDroits[$wName])) {
                    $widget->setView($this->listDroits[$wName]['view']);
                    $widget->setEdit($this->listDroits[$wName]['edit']);
                } else {
                    $widget->setView(false);
                    $widget->setEdit(false);
                }
            }
        }
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    /*
     * Gestion des erreur du formulaire
     */

    public function addError($libelle, $widgetName = false) {
        if ($widgetName === false) {
            $this->errors[] = $libelle;
            $this->valid = false;
        } else {
            //TODO: implemente les erreurs globale au formulaire
            if (!isset($this->widgetList[$widgetName])) {
                throw new mmException("Le widget $widgetName n'existe pas");
            }
            try {
                $this->widgetList[$widgetName]->addError($libelle);
            } catch (mmExceptionWidget $e) {
                $this->valid = false;
                $this->errors[] = "$widgetName : $libelle";
            }
        }
    }

    /*
     * Methode utilitaire
     */

    /**
     * disable all field of the form
     * @param boolean $butButton doesn't disable buttons if true (default)
     * @param boolean $deep disable sub form if true (default)
     */
    public function disable($butButton = true, $deep = true) {
        //widget
        foreach ($this->widgetList as $w) {
            if (!$w instanceof mmWidgetButton || !$butButton) {
                $w->disable();
            }
        }
        //on traite les sous formulaires
        if ($deep) {
            foreach ($this->formsList as $form) {
                if ($form instanceof mmForm) {
                    $form->disable($butButton);
                } else {
                    if (is_array($form)) {
                        foreach ($form as $subForm) {
                            if ($subForm instanceof mmForm) {
                                $subForm->disable($butButton);
                            } else {
                                throw new mmExceptionDev('array de sous forme contenant un truc pas legal');
                            }
                        }
                    } else {
                        throw new mmExceptionDev('pas mmForm et pas array');
                    }
                }
            }
        }
    }

    /**
     * enable all field of the form
     * @param boolean $butButton doesn't disable buttons if true (default)
     * @param boolean $deep disable sub form if true (default)
     */
    public function enable($butButton = true, $deep = true) {
        //widget
        foreach ($this->widgetList as $w) {
            if (!$w instanceof mmWidgetButton || !$butButton) {
                $w->enable();
            }
        }
        //on traite les sous formulaires
        if ($deep) {
            foreach ($this->formsList as $form) {
                if ($form instanceof mmForm) {
                    $form->enable($butButton);
                } else {
                    if (is_array($form)) {
                        foreach ($form as $subForm) {
                            if ($subForm instanceof mmForm) {
                                $subForm->enable($butButton);
                            } else {
                                throw new mmExceptionDev('array de sous forme contenant un truc pas legal');
                            }
                        }
                    } else {
                        throw new mmExceptionDev('pas mmForm et pas array');
                    }
                }
            }
        }
    }

    /*
     * Gestion de l'acces de type tableaux
     */

    public function offsetGet($offset) {
        if (isset($this->widgetList[$offset])) {
            return $this->widgetList[$offset];
        } else {
            if (isset($this->formsList[$offset])) {
                return $this->formsList[$offset];
            } else {
                throw new mmExceptionDev("Le widget $offset n'existe pas");
            }
        }
    }

    public function offsetExists($offset) {
        return isset($this->widgetList[$offset]);
    }

    public function offsetSet($offset, $value) {
        if (isset($this->widgetList[$offset])) {
            try {
                $this->widgetList[$offset]->setValue($value);
                if ($this->record) {
                    $this->record[$offset] = $this->widgetList[$offset]->getDbValue();
                }
            } catch (mmExceptionWidget $e) {
                $this->valid = false;
            }
        } else {
            throw new mmExceptionDev("Le widget $offset n'existe pas");
        }
    }

    public function offsetUnset($offset) {
        unset($this->widgetList[$offset]);
    }

}
