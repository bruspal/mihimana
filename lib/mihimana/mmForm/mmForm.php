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
    protected $nameFormat = '%s',
            $prefixName = '',
            $record = null,
            $valid = true,
            $options = array(),
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
            $modified;

    public function __construct(Doctrine_Record $record = null, $options = array()) {
        $this->new = true;
        if ($record) {
            $this->setWidgetFromRecord($record);
            $this->new = !$this->record->exists();
        }
        $this->option = $options;
//    if (myUser::superAdmin() && $this->adminMenu) {
//      $this->addJavascript('admin_menu', mdTemplate::renderTemplate('jsAdmin.js', array('baseUrl'=>$_SERVER['SCRIPT_NAME'].'/Droits/', 'screen' => '')));
//    }
    }

    public function __toString() {
        return $this->render();
    }

    public function setAction($action, $encodeUrl = true) {
        if ($encodeUrl) {
            $action = genereUrlProtege($action);
        }
        $this->action = $action;
    }

    public function getAction() {
        return $this->action;
    }

    public function setMethod($method) {
        $method = strtolower($method);
        if ($method == 'post' || $method == 'get') {
            $this->method = $method;
        } else {
            throw new mmExceptionDev("La method pour un formulaire ne peut etre que 'post' ou 'get'");
        }
    }

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
     * Ajoute ou remplace le widget identifie par $name
     * 
     * @param string $name nom du widget
     * @param mmWidget $widget widget
     */
    public function addWidget(mmWidget $widget, $ignoreNameFormat = false) {
        $name = $widget->getName();
        //Mise a jour auto du label
        $widget->setLabel(ucfirst(str_replace('_', ' ', $name)));
        if (!$ignoreNameFormat) {
            $widget->setNameFormat(sprintf($this->nameFormat, $name));
        }
        $widget->setContainer($this);
        $widget->postAddWidget();
        if ($this->getId()) {
            $widget->setId($this->getId().'_'.$widget->getId());
        }
        $this->widgetList[$name] = $widget;
    }

    public function addForms($subForm, $includeName, $index = false) {
        if ($subForm instanceof mmForm) {
            if ($includeName) {
                if ($index === false) {
                    $subForm->setNameFormat(sprintf('%s[%s][%%s]', $this->name, $includeName));
                    $baseId = $includeName;
                } else {
                    $subForm->setNameFormat(sprintf('%s[%s][%s][%%s]', $this->name, $includeName, $index));
                    $baseId = $includeName . '_' . $index;
                }
                //$subForm->setAdminMenu(false);
                foreach ($subForm->widgetList as $wn => $w) {
                    $w->setId($baseId . '_' . $w->getId());
                    //$w->setAdminMenu(false);
                }
                if ($index !== false) {
                    $this->formsList[$includeName][$index] = $subForm;
                } else {
                    $this->formsList[$includeName] = $subForm;
                }
            }
//      $this->formsList[$includeName] = $subForm;
        } else {
            if (is_array($subForm)) {
                if (trim($includeName) == '') {
                    throw new mmExceptionDev('Formulaire imbriqu&eacute; : Dans le cas d\'imbrication de tableau de formulaire, $includeName doit etre fournis');
                }
                //on a fournis un tableau de formulaire
                foreach ($subForm as $k => $sf) {
                    if (!$sf instanceof mmForm) {
                        throw new mmExceptionDev('Formulaire imbriqu&eacute; : le tableau de formulaire imbrique contien une donn&eacute;e de type non mdForm');
                    }
                    $sf->setNameFormat(sprintf('%s[%s][%s][%%s]', $this->name, $includeName, $k));
                    //$sf->setAdminMenu(false);
                    foreach ($sf->widgetList as $wn => $w) {
                        $w->setId($includeName . '_' . $k . '_' . $w->getId());
                    }
                }
                $this->formsList[$includeName] = $subform;
            } else {
                throw new mmExceptionDev('Formulaire imbriqu&eacute; : seul un mdForm ou un tableau de mdForm peut etre imbriqu&eacute;');
            }
        }
//    if ($includeName)
//    {
//      $this->formsList[$includeName] = $subForm;
//    }
//    else
//    {
//      $name = strSlugify($subForm->getNameFormat());
//      $this->formsList[$name] = $subForm;
//    }
    }

    public function setWidgets(array $widgets) {
        foreach ($widgets as $widget) {
            $this->addWidget($widget);
        }
//    $this->widgetList = $widgets;
    }

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

            //Application des validateur
            if (isset($field['primary']) && $field['primary']) {
                $widget->addValidation('notnull');
            }
            if (isset($field['length']) && $widget instanceof mmWidgetText) {
                $widget->addValidation('length_max', $field['length']);
            }
            if (isset($field['notnull']) && $field['notnull']) {
                $widget->addValidation('notnull', $field['notnull']);
//        $widget->setLabel('<strong>*</strong> '.$widget->getLabel());
            }
            if (isset($field['default'])) {
                $widget->setDefault($field['default']);
            }

            //Affectation du widget
//      $this->widgetList[$fieldName] = $widget;
            $this->addWidget($widget);
        }
        $this->record = $record;
        return $this->valid;
    }

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

    /**
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
     * set values to the form, $values is a associative array 'field_name' => 'value'
     * @param array $values
     * @return boolean true if all fields are validated false otherwise
     */
    public function setValues($values = array()) {
        $this->valid = true;
        /*
         * Because of the behaviour of foreach regarding arrayAccess implementation, mmVarHolder is internally turned to an array (BUG #34445 : https://bugs.php.net/bug.php?id=34445)
         */
        if ($values instanceof mmVarHolder) {
            $_values = $values->toArray();
        } else {
            $_values = & $values;
        }
        foreach ($_values as $name => $value) {
            if (isset($this->widgetList[$name])) {
                try {
                    //On verifie les droits
//          if (myUser::superAdmin() || isset($this->listDroits[$name]['edit']) && $this->listDroits[$name]['edit']) {
                    if ($value == "O") {
                        $rien = $value;
                    }
                    $this->widgetList[$name]->setValue($value);
//          }
                } catch (mmExceptionWidget $e) {
                    $this->valid = false;
                }
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

    public function setValue($fieldName, $value) {
        if (isset($this->widgetList[$fieldName])) {
            try {
                //On verifie les droits
//        if (myUser::superAdmin() || isset($this->listDroits[$name]['edit']) && $this->listDroits[$name]['edit']) {
                $this->widgetList[$fieldName]->setValue($value);
//        }
            } catch (mmExceptionWidget $e) {
                $this->valid = false;
            }
        }
    }

    public function getValues() {
        $result = array();
        foreach ($this->widgetList as $wn => $widget) {
            $result[$wn] = $widget->getValue();
        }
        return $result;
    }

    public function getValue($fieldName) {
        return $this->widgetList[$fieldName]->getValue();
    }

    // a virer et $this->prefixName aussi
    public function setPrefixName($prefix) {
        $this->prefixName = $prefix;
    }

    public function getPrefixName() {
        return $this->prefixName;
    }

    // jusque la

    public function setName($name) {
        $this->name = $name;
        //On met a jour le nameFormat
//    $this->nameFormat = sprintf()
    }

    public function getName() {
        return $this->name;
    }

    public function getTableName() {
        if ($this->record === null) {
            return '';
        } else {
            return $this->record->getTable()->getTableName();
        }
    }

    public function setId($id = '') {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setScreen($screen) {
        $this->screen = $screen;
    }

    public function getScreen() {
        return $this->screen;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

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

    public function setNameFormat($format) {
        $this->nameFormat = $format;
        //On applique le nouveau nameFormat a tous les widgetAssocie
        foreach ($this->widgetList as $wn => $w) {
            $w->setNameFormat($this->nameFormat);
        }
    }

    public function getNameFormat() {
        return $this->nameFormat;
    }

    /**
     * rendu du formulaire
     * 
     * @param array $fieldList : $array list des champs a afficher, si omis, affiche tous les champs
     * @param array $subFormsSetting: tableau de paramettrage de rendu des sous-formulaires array(nom_interne =><br />
     * array('fieldList'=>comme $fieldList, 'renderType'=> type de rendu, 'label'=> label a afficher dans le cas du renderType='fieldset'))<br />
     * type de rendu:<ul><li>table (default): rend le formulaire comme une table html</li><li>fieldset: rend le formulaire comme avec renderFieldset</li><li>row: rend le formulaire dans une table horizontale</li></ul>
     * @return type 
     */
    public function render($fieldList = null, $subFormsSetting = null) {
        $fieldList = $this->generateFieldsList($fieldList);
        $result = '';
        foreach ($fieldList as $wn => $widget) {
            if (($wn == 'id' && $widget instanceof mmWidgetHidden) || $widget instanceof mmWidgetHidden) { //TODO: ameliorer le filtre pour rendre par automatique les champs index invisible
                $result .= '<tr style="display: none;"><td colspan="2">' . $widget->render() . '</td></tr>';
            } else {
                if (!is_a($widget, 'mdWidgetButton')) {
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
        //On ajoute les balise <form> si l'action a ete definie
        if ($this->action) {
            $result = $this->start().$result.$this->stop();
        }
        $result .= $this->renderJavascript($fieldList);
        return $result;
    }

    /**
     * start form
     * @return string
     */
    public function start() {
        return sprintf('<form action="%s" method="%s" %s enctype="%s">', $this->action, $this->method, $this->id == '' ? '' : 'id="'.$this->id.'"', $this->enctype);
    }

    /**
     * close the form zone
     * @return string
     */
    public function stop() {
        return '</form>';
    }

    public function renderFormHeader() {
        deprecatedMethode(__CLASS__, __METHOD__, 'start');
        throw new mmExceptionDev('Method renderFormHeader desuete. Utiliser start()');
    }

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

    public function renderFieldset($legend = '', $fieldList = null, $subFormsSetting = null) {
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
    protected function renderSubForms($subFormsSetting = null) {
        if ($subFormsSetting == null)
            return'';

        $result = '';
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
            //En enregistrement existe pour ce formulaire, on fais un enregistrement
            if ($this->valid && $this->updateRecord()) {
                if ($this->record->isModified(true)) {
                    //si les données ont été changées dans l'enregistrement ou un de ses enregistrement lié, on enregistre
                    $this->record->save($conn);
                }
                $this->new = !$this->record->exists();
            }
        }

        //Le formulaire n'as pas d'enregistrement associé mais peut etre que ses eventuel sous formulaire en ont.
        //Dans ce cas la on effectue une sauvegarde
        foreach ($this->formsList as $form) {
            if ($form instanceof mmForm) {
                $form->save($conn);
            } else {
                if (is_array($form)) {
                    foreach ($form as $sf) {
                        $sf->save();
                    }
                } else {
                    throw new mmExceptionDev('mdForm::save: Formulaire imbrique doit etre du type mdForm ou array');
                }
            }
        }

        return $this->record;
    }

    /**
     * retourne l'objet doctrine stocker dans le formulaire avec les donnees en cours
     * @return type 
     */
    public function getRecord() {
        return $this->record;
    }

    /*
     * Gestion des droits
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

    public function addError($libelle, $widgetName = '') {
        //TODO: implemente les erreurs globale au formulaire
        if (!isset($this->widgetList[$widgetName])) {
            throw new mmException("Le widget $widgetName n'existe pas");
        }
        try {
            $this->widgetList[$widgetName]->addError($libelle);
        } catch (mmExceptionWidget $e) {
            $this->valid = false;
        }
    }

    /*
     * Methode utilitaire
     */

    /**
     * Met en disabled l'ensemble des champs
     */
    public function disable($saufBouton = true) {
        foreach ($this->widgetList as $w) {
            if (!$w instanceof mmWidgetButton || !$saufBouton) {
                $w->disable();
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