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
  @file : mmWidget.php
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

class mmWidget extends mmObject {

    protected $attributes = array();  //Tableau des attibuts
    protected $label = ''; // libelle
    protected $default = '';  //Valeur initial (copie de value jamais modifier)
    protected $dbValue = ''; //Valeur nettoyee
    protected $display = true; //A true on affiche le widget, a false non
    //Tableau de clause de verification et validation
    protected $validations = array();
    protected $errors = array();
    protected $jsError = array();
    protected $options = array();
    // a vrai si le champs viens de la base de donnees a faux sinon
    protected $fromDb = false;
    protected $nameFormat = '%s';
    protected $edit = true, $view = true, $editOrig = true, $viewOrig = true, $enabled = true;
    protected $javascripts = array();
    protected $adminMenu = true; //A vrai genere le menu admion si on est admin a false ne le genere pas (pour eviter les boucle infinie)
    protected $info = '';
    protected $min = null, $max = null;
    public $containerForm = null;
    public $rendered = false;
    protected $ignorePortefeuille = false;

    /**
     * Cree un nouveau widget
     * 
     * @param type $name nom du widget
     * @param type $type type du widget
     * @param type $value valeur par default a la creation
     * @param type $attributes tablleaux des attributs
     */
    public function __construct($name, $type = 'input', $value = '', $attributes = array(), $options = array()) {
        if (is_string($name)) {
            $this->default = $value;
            //TODO: mettre tous les attribut name, type, value dans la list des attribut
            $this->attributes['name'] = $name;
            $this->attributes['type'] = $type;
            $this->attributes['value'] = $value;

            $this->attributes['id'] = strSlugify($name) . '_id';
            $this->addAttributes($attributes);
            return $this;
        }
        if ($name instanceof mmWidget) {
            $class = $this->attributes['class'];
            $this->default = $name->default;
            $this->attributes = array_merge($name->attributes, $attributes);
            $this->label = $name->label;
            $this->dbValue = $name->dbValue;
            $this->display = $name->display;
            $this->validations = $name->validations;
            $this->errors = $name->errors;
            $this->jsError = $name->jsError;
            $this->options = $name->options;
            $this->fromDb = $name->fromDb;
            $this->nameFormat = $name->nameFormat;
            $this->javascripts = $name->javascripts;
            $this->validations = $name->validations;
            //on ecrase le type de widget et la classe
            $this->attributes['type'] = $type;
            $this->attributes['class'] = $class;
            $this->enabled = $name->enabled;
            return $this;
        }
        throw new mmExceptionDev(sprintf('%s $name doit etre soit une chaine soit un mmWidget', __METHOD__));
    }

    public function __toString() {
        return $this->render();
    }

    /**
     * remplace les attributs existants
     * 
     * @param array $values tableau des attribut sous le format array('nom attribut' => 'valeur attribut',...)
     */
    public function setAttributes($values) {
        $this->attributes = $values;
    }

    /**
     * Desactive un champ
     * @param boolean $hardDisable si positionner a true(défaut) active aussi la descativation interne (c'est a dire que le widget sera rendu comme du text pure). sinon si vaut false effectue seulement une descativation html
     */
    public function disable($hardDisable = true) {
        $this->addAttribute('disabled', 'disabled');
        if ($hardDisable) {
            $this->enabled = false;
        }
    }

    /**
     * Active un champ
     */
    public function enable() {
        $this->delAttribute('disabled');
        $this->enabled = true;
    }

    public function isEnabled() {
        return $this->enabled && !isset($this->attributes['disabled']);
    }

    /**
     * ajoute/remplace un attribut a la liste des attribut
     * @param type $name nom de l'attribut
     * @param type $value  valeur de l'attribut
     */
    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function addCssClass($class) {
        if (!isset($this->attributes['class']) || $this->attributes['class'] == '') {
            $this->attributes['class'] = $class;
        } else {
            $this->attributes['class'] .= ' ' . $class;
        }
    }

    public function addCssStyle($style) {
        if (empty($this->attributes['style'])) {
            $this->attributes['style'] = $style;
        } else {
            $this->attributes['style'] .= '; ' . $style;
        }
    }

    /**
     * Ajoute ou remplace les attributs par ceux fournis dans value
     * 
     * @param type $values tableau des attribut sous le format array('nom attribut' => 'valeur attribut',...)
     */
    public function addAttributes($values) {
        $this->attributes = array_merge($this->attributes, $values);
    }

    /**
     * supprime un attribut de la list des attributs, genere une exception si l'attribut n'existe pas
     * 
     * @param string $name nom de l'attribut a supprimer
     */
    public function delAttribute($name) {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * retourne la valeur de l'attribut $name sinon retourne ''
     * @param type $name 
     * @return type
     */
    public function getAttribute($name) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return '';
    }

    /**
     * retourne true si l'attribut existe, false sinon
     * @param type $name
     * @return type 
     */
    public function hasAttribute($name) {
        if (isset($this->attributes[$name])) {
            return true;
        }
        return false;
    }

    /**
     * set le label
     * @param string $label 
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * retourne le label
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    public function setId($id) {
        $this->attributes['id'] = $id;
    }

    public function getId() {
        return $this->attributes['id'];
    }

    public function setNameFormat($nameFormat) {
        $this->nameFormat = $nameFormat;
    }

    public function getNameFormat() {
        return $this->nameFormat;
    }

    /**
     * Met a jour le champ a partir de donner fournis au format humain (par exemple via formulaire
     * @param type $value 
     */
    public function setValue($value, $ignoreControle = 0) {
        $this->attributes['value'] = $value;
        if (!$ignoreControle) { //si on ignore pas les controles
            $this->checkValidations(); //appel la procedure de validation du champ
        }
        $this->clean();
    }

    /**
     * Retourne la valeur du champ en etat actuel
     * @return mixed
     */
    public function getValue() {
        return $this->attributes['value'];
    }

    public function getStyle() {
        if (!empty($this->attributes['style'])) {
            return $this->attributes['style'];
        } else {
            return '';
        }
    }

    public function setDbValue($value) {
        $this->dbValue = $value;
        $this->dbClean();
    }

    public function getDbValue() {
        return (string) $this->dbValue;
    }

    public function setMax($valeur) {
        $this->max = $valeur;
    }

    public function getMax() {
        return $this->max;
    }

    public function setMin($valeur) {
        $this->min = $valeur;
    }

    public function getMin() {
        return $this->min;
    }

    public function setName($name) {
        $this->attributes['name'] = sprintf($this->nameFormat, $name);
    }

    public function getName() {
        return $this->attributes['name'];
    }

    public function getFromDb() {
        return $this->fromDb;
    }

    public function setFromDb($bool) {
        $this->fromDb = $bool;
    }

    public function setInfo($strInfo) {
        $this->info = $strInfo;
    }

    public function getInfo() {
        return $this->info;
    }

    public function setContainer(mmForm $container) {
        $this->containerForm = $container;
    }

    public function getContainer() {
        return $this->containerForm;
    }

    /**
     * Nettoie la valeur et la stock dans $this->dbValue pour la rendre DB comprehensible
     * Pour la securite toujours commencer par appeler l'ancetre dans les classes derive
     * TODO: mettre la secu en place
     */
    public function clean() {
        $this->dbValue = $this->attributes['value'];
    }

    /**
     * Nettoie la valeur et la stock dans $this->dbValue pour la rendre humain comprehensible
     * Pour la securite toujours commencer par appeler l'ancetre dans les classes derive
     */
    public function dbClean() {
        $this->attributes['value'] = $this->dbValue;
    }

    public function setDefault($value) {
        $this->default = $value;
    }

    public function getDefault() {
        return $this->default;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function addError($message, $dbValue = null) {
        $this->errors[] = $message;
        if ($dbValue !== null) {
            $this->dbValue = $dbValue;
        }
        throw new mmExceptionWidget($message);
    }

    public function isValid() {
        return count($this->errors) == 0;
    }

    public function setView($view) {
        $this->view = $view;
        $this->viewOrig = $view;
    }

    public function setEdit($edit) {
        $this->edit = $edit;
        $this->editOrig = $edit;
    }

    public function isEditable() {
        return $this->edit && $this->enabled && !isset($this->attributes['disabled']);
    }

    /**
     * Fais le rendu HTML du widget
     * 
     * @param type $extraAttributes
     * @param type $replace 
     */
    public function render($extraAttributes = array(), $replace = false) {

        //Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        //Gestion de 'laffichage ou non
        if ($this->rendered)
            return '';

        if ($this->edit && $this->enabled) {
            $this->addResultClass();
            $result = sprintf('<input type="%s" name="%s" value="%s" %s />', $this->attributes['type'], sprintf($this->nameFormat, $this->attributes['name']), $this->attributes['value'], $this->generateAttributes($extraAttributes, $replace));
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                $result = sprintf('<span %s>%s</span>', $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']);
            } else {
                $result = sprintf('<span %s>&nbsp;</span>', $this->generateAttributes($extraAttributes, $replace));
            }
        }
        //On marque le champ comme rendu pour indiquer que le widget a ete rendu et eviter le rendu multiple
        $this->rendered = true;
        return $result . $this->renderInfo() . $this->renderAdminMenu();
    }

    public function renderPdf($extraAttributes = array(), $replace = false) {

        //Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        //Gestion de 'laffichage ou non
        if ($this->rendered)
            return '';

        if ($this->view || $this->edit) {
            $result = sprintf('<span>%s</span>', $this->attributes['value']);
        }
        //On marque le champ comme rendu pour indiquer que le widget a ete rendu et eviter le rendu multiple
        $this->rendered = true;
        return $result;
    }

    public function renderIf($condition, $attributes = array(), $replace = false) {
        if ($condition) {
            return $this->render($attributes, $replace);
        }
    }

    public function renderIfElse($condition, $trueAttributes = array(), $falseAttributes = array(), $trueReplace = false, $falseReplace = false) {
        if ($condition) {
            return $this->render($trueAttributes, $trueReplace);
        } else {
            return $this->render($falseAttributes, $falseReplace);
        }
    }

    /**
     * renvois le label du widget
     * @return string
     */
    public function renderLabel() {
        //idem pour la gestion des droits
        $droits = true;

        return $this->display && $droits ? $this->label : '';
    }

    public function renderInfo() {
        $strHelp = '';
        if ($this->info) {
            $strHelp = sprintf('<div id="a_%s" style="display: none;" class="mdHelpBubble">%s</div>', $this->attributes['id'], $this->info);
            $strHelp .= sprintf("<script type=\"text/javascript\">$('#%s').ready(function(){showHelp('%s')})</script>", $this->attributes['id'], $this->attributes['id']);
        }
        return $strHelp;
    }

    /**
     * 
     */
    public function renderText($extraAttributes = array(), $replace = false) {

        if ($this->edit || $this->view) {
            return sprintf('<span %s>%s</span>', $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']) . $this->renderAdminMenu();
        }
        return '';
    }

    public function renderTextRow($extraAttributes = array(), $replace = false) {

        if ($this->view) {
            return sprintf('<tr><th>%s</th><td>%s</td></tr>', $this->renderLabel(), $this->renderText());
        }
    }

    /**
     * Genere une ligne de type <tr>label</tr><td>valeur</td>.
     * @param type $option : options additionnel au widget
     * @param type $renderRow : a true genere la ligne du tableau meme si le champs ne doits pas etre afficher.<br />Dans le cas contraire, si le champs ne doit pas etre afficher on genere pas le <tr>...
     * @return string 
     */
    public function renderRow($option = array(), $renderRow = false) {

        if ($this->rendered)
            return '';
        $result = '';
        //rendu
        if ($this->edit) {
            return sprintf('<tr><th>%s</th><td>%s%s</td></tr>', $this->renderLabel(), $this->render(), $this->renderErrors());
        } else {
//      if ($renderRow) {
            if ($this->view) {
                return sprintf('<tr><th>%s</th><td>%s</td></tr>', $this->renderLabel(), $this->render());
            } else {
                if ($renderRow) {
                    return '<tr><th>&nbsp;</th><td>&nbsp;</td></tr>';
                } else {
                    return '';
                }
            }
//      }
//      else {
//        if ($this->view) {
//          return sprintf('%s %s', $this->renderLabel(), $this->render());
//        }
//        else {
//          return '';
//        }
//      }
        }

        return $result;
    }

    public function renderErrors() {
        $result = '<span class="errors" id="er_' . $this->getId() . '">';
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $result .= sprintf('%s<br />', $error);
            }
        }
        return $result.='</span>';
    }

    /**
     * Menu d'administration
     */
    public function renderAdminMenu() {
        return '';

        if (!myUser::superAdmin() || !$this->adminMenu /* || $this->attributes['name'] == 'id' */)
            return '';
//    $result = mdTemplate::renderTemplate('widgetAdmin.php');
        ob_start();
        require (dirname(dirname(__FILE__))) . '/templates/widgetAdmin.php';
        $result = ob_get_clean();

        return $result;
    }

    //fonction d'activation du mode admin ou non
    public function setAdminMenu($admin) {
        $this->adminMenu = $admin;
    }

    //Code de verification
    public function addValidation($name, $params = array()) {
//    if ( ! method_exists($this, $name.'_php') || ! method_exists($this, $name.'_js'));
//    {
//      throw new mdExceptionControl("le validateur $name n'est pas définie");
//    }
//    if ( ! ($this instanceof mdWidgetHidden)) {
//      $methodName = $name.'_pre';
//      $this->$methodName($params);
//    }
        if (!is_array($params)) {
            $params = array($params);
        }
        $this->validations[$name] = $params;
        if (!($this instanceof mmWidgetHidden)) {
            $methodName = 'js_validator_' . $name;
            call_user_func_array(array($this, $methodName), $params);
        }
    }

    /**
     * Genere le script associe au widget
     * 
     */
    public function renderJavascript($inDocumentReady = false, $forceRender = false) {
//    if ( (! $this->rendered && ! $forceRender) || ! $forceRender)
        if (!$this->rendered) {
            //Le widget n'a pas été rendu a l'écran ? on ne rend pas le script. sauf si on le force
            return '';
        }
        $result = '';
        $head = '';
        $foot = '';
        if ($inDocumentReady) {
            $head = '$(document).ready(function(){';
            $end = '});';
        }
        foreach ($this->javascripts as $script) {
            $result .= $script;
        }
        return $head . $result . $foot;
    }

    public function addJavascript($name, $script) {
        $this->javascripts[$name] = $script;
    }

    public function getJavascript($name) {
        return $this->javascripts[$name];
    }

    public function getJavascripts() {
        return $this->javascripts;
    }

    public function setDisplay($display = true) {
        $this->display = $display;
    }

    /*
     * Gestion du portefeuille sorte de 'plugin'
     */

    public function _old_setDroitsParPortefeuilles() {
        $this->view = true;
        $this->edit = true;
        return true;

        if (!$this instanceof mmWidgetButton && !myUser::superAdmin() && !$this->ignorePortefeuille) {
            if (!myUser::portefeuillePeuxCreer() && !myUser::portefeuillePeuxModifier()) {
                //pas le droit d'editer
                $this->edit = false;
                //droit de voir ?
                if (!myUser::portefeuillePeuxVisualiser()) {
                    $this->view = false;
                }
            }
        }
    }

    public function _old_ignorePortefeuille() {
        $this->ignorePortefeuille = true;
    }

    /*
     * Rendu static
     */

    public static function _old_draw($name, $value, $attributes = array()) {
        $className = get_called_class();
        if ($className == 'mdWidget') {
            $widget = new $className($name, '', $value, $attributes);
        } else {
            $widget = new $className($name, $value, $attributes);
        }
        return $widget->render();
    }

    public static function _old_drawIf($condition, $name, $value, $attributes = array()) {
        if ($condition) {
            return static::draw($name, $value, $attributes);
        }
    }

//  public static function drawIfNot($condition, $name, $value, $attributes = array()) {
//    return static::drawIf( ! $condition, $name, $value, $attributes);
//  }

    public static function _old_drawIfElse($condition, $name, $value, $trueAttributes = array(), $falseAttributes = array()) {
        if ($condition) {
            return static::draw($name, $value, $trueAttributes);
        } else {
            return static::draw($name, $value, $falseAttributes);
        }
    }

    /*
     * Foncton interne de gestion 
     */

    /**
     * genere la chaine correspondante a la liste des attributs
     * 
     * @param type $extraAttributes attribut ajouter aux attribut deja existant. Si un des attribut existe deja il est remplace par celui de $extraAttributes
     * @param type $replace si a true, les attributs fournis remplace ceux existant, sinon ajout/remplacement des attributs
     * @return string
     */
    protected function generateAttributes($extraAttributes = array(), $replace = false) {
        if ($replace) {
            $attributes = $extraAttributes;
        } else {
            $attributes = array_merge($this->attributes, $extraAttributes);
        }
        //On vire les attribut 'speciaux'
        unset(
                $attributes['name'], $attributes['type'], $attributes['value']
        );

        $result = '';

        foreach ($attributes as $an => $a) {
            $result .= sprintf(' %s="%s"', $an, $a);
        }
        return substr($result, 1);
    }

    /**
     * Traitement a effectuer apres l'ajout du widget dans un formulaire (appelé par mdForm::addWidget) 
     */
    public function postAddWidget() {
        //rien car par defaut on fait rien. ce sont les decendant qui doivent gerer ca
    }

    /**
     * Verifie les regle de validation definie dans $this->validations (traitement generique genre not null et etc
     */
    public function checkValidations() {
        //validation des min/max
        if (!is_null($this->min) && $this->getValue() < $this->min) {
            $this->addError("La valeur ne peux etre inferieur à " . $this->min);
        }
        if (!is_null($this->max) && $this->getValue() > $this->max) {
            $this->addError("La valeur ne peux etre superieur à " . $this->max);
        }

        foreach ($this->validations as $validatorName => $params) {
            $methodName = 'php_validator_' . $validatorName;
            if (method_exists($this, $methodName)) {
                call_user_func_array(array($this, $methodName), $params);
            } else {
                throw new mmExceptionControl("la validation $validatorName n'est pas definie.");
            }
        }
    }

    /**
     * Ajoute le nom de class mdError en cas d'erreur ou mdOk si pas d'erreur
     * 
     * @param type $class class a ajouter en cas d'erreur 'error' par default
     */
    protected function addResultClass() {

        $class = $this->isValid() ? '' : 'mdError';

        if (isset($this->attributes['class']) && $this->attributes['class']) {
            //la class existe deja et pas vide on ajoute
            $this->attributes['class'] .= ' ' . $class;
        } else {
            //sinon on cree
            $this->attributes['class'] = $class;
        }
    }

    /*     * *************************
     * methode de validation PHP
     * ************************* */

    public function php_validator_notnull() {
        if ($this->attributes['value'] === '') {
            $this->addError("le champ " . $this->getName() . " est obligatoire", '');
            $this->label = sprintf('<span class="notnull">%s</spam>', $this->label);
        }
    }

    public function php_validator_length_max($params) {
        if (strlen($this->attributes['value']) > $params) {
            $this->addError("La longueur ne peux pas etre superieur a $params", '');
        }
    }

    public function php_validator_integer() {
        if ( ! filter_var($this->attributes['value'], FILTER_VALIDATE_INT)) {
            $this->addError('Le champ doit etre un entier', 0);
        }
    }

    public function php_validator_real() {
        if ( ! filter_var($this->attributes['value'], FILTER_VALIDATE_FLOAT)) {
            $this->addError('Le champ doit etre une valeur numerique', 0);
        }
    }
    
    public function php_validator_email() {
        if ( ! filter_var($this->attributes['value'], FILTER_VALIDATE_EMAIL)) {
            $this->addError('Adresse email invalide');
        }
    }

    /*     * **********************************
     * fonction de validation javascript
     * ********************************** */

    public function js_validator_notnull() {
        $this->addJavascript('__notnull', "mmJsCheckNotnull($('#{$this->attributes['id']}'));\n");
    }

    public function js_validator_length_max($params) {
        $this->addJavascript('__length_max', "mmJsCheckLengthMax($('#{$this->attributes['id']}'), {$params});\n");
    }

    public function js_validator_integer() {
        $this->addJavascript('__integer', "mmJsCheckInteger($('#{$this->attributes['id']}'));\n");
    }

    public function js_validator_real($rule) {
        $this->addJavascript('__real', "mmJsCheckReal($('#{$this->attributes['id']}'));\n");
    }
    
    public function js_validator_email() {
        $this->addJavascript('__email', "mmJsCheckEmail($('#{$this->attributes['id']}'));\n");
    }

}