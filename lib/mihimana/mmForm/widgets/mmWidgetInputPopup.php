<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmForm/widgets
@file : mmWidgetInputPopup.php
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


class mmWidgetInputPopup extends mmWidgetText {
  
  protected
          $libelle,
          $options,
          $affBouton;
  
  public function __construct($name, $options, $libelle = '...', $value = '', $attributes = array()) {
    $this->libelle = $libelle;
    parent::__construct($name, $value, $attributes);
    $resAnalyseOptions = $this->analyseOptions($options);
    $this->affBouton = $resAnalyseOptions;
  }
  
  public function analyseOptions($options)
  {
    $this->options = $options;
    
    if ( ! isset($options['table']) || $options == '')
    {
      mmUser::flashError($this->getName()." aucun nom de table fournis. Veuillez indiquer table=nom de la table.");
      return false;
//<----- Sortie ici. en renvoie faux pour signaler l'erreur
    }
    
    if ( ! isset($options['cle']) || $options['cle'] == '')
    {
      $options['cle'] = mmSQL::getCleUnique($options['table'], true);
    }
      
    if ( ! isset($options['libelle']) || $options['libelle'] == '')
    {
      $options['libelle'] = $options['cle'];
    }

    try {
      //on effectue l'analyse des resultats avec les données des options
      $table = Doctrine_Core::getTable($options['table']);
      $selectTest = mmParseSqlConcat($options['cle'], $options['table']);
      $reqTest = $table->createQuery()->select($selectTest)->where('0=1')->execute();
      $selectTest = mmParseSqlConcat(str_replace(',', '+', $options['libelle']), $options['table']); //Petit hack on transforme les , par des + et on effectue l'analyse des libelle + execution. si une exception surviens c'est qu'il y'a une couille
      $reqTest = $table->createQuery()->select($selectTest)->where('0=1')->execute();
    }
    catch (Doctrine_Exception $e)
    {
      $code = $e->getCode();
      switch ($code)
      {
        case 0:
          mmUser::flashError($this->getName().": Erreur d'acces aux données: la table $table n'existe pas.");
          break;
        case 42:
          mmUser::flashError($this->getName().": Nom de colonne inconnue dans le parametre cle ou libelle");
          break;
        case 42000:
          mmUser::flashError($this->getName().": Erreur de parametrage de la cle");
          break;
        default:
          mmUser::flashError($this->getName().": Erreur inconnue code $code");
          break;
      }
      if (DEBUG) throw $e;
      return false;
    }
    return true;
  }
  
  public function postAddWidget()
  {
    
  }
  
  public function render($extraAttributes = array(), $replace =  false, $extra = '') {
    //construction de l'url
    
    if ($this->affBouton)
    {
//      $bouton = new mdWidgetButtonAjaxPopup($this->libelle, $url, 'r_'.$this->getName());
      $this->options['widget'] = $this->getId();
      $this->options['vi']     = '';
      $chaineOption = $this->options->getChaine(true);

//      $url = "?module=pInputPopup&o=$chaineOption;vi='+$('#".$this->getId()."').val()";
      $url = "?module=pInputPopup&o=$chaineOption";

//mdAjaxHtmlDialog('?module=pInputPopup&o=table%3Dficadr%3Bcle%3Dp101%3Bcols%3Dp101%2Cp104%2B+%2Bp105%3Blibelle%3DNumero%2CNom+pr%C3%A9nom%3Bwidget%3Dpcie_01_id;vi='+$('#pcie_01_id').val()')      
      $bouton = new mmWidgetButtonAjaxPopup($this->libelle, $url, 'r_'.$this->getName());
//      $bouton = new mdWidgetButton('r_'.$this->getName(), '', $this->libelle, $url, );
//      $htmlBouton = sprintf('<input type="button" value="%s" onclick="mdAjaxHtmlDialog(\'?module=pInputPopup&o=%s\'+$("#%s").val())" />',
      $htmlBouton = sprintf('<input type="button" value="%s" onclick="mdAjaxHtmlDialog(\'?module=pInputPopup&o=%s\')" />', //;vi=\'+$("#%s").val()
              $this->libelle, $chaineOption, $this->getId());
//      $htmlBouton = $bouton->render();
    }
    else
    {
      $htmlBouton = '';
    }
    //On genere l'input proprement dit suivie du bouton de recherche
    $html = parent::render($extraAttributes, $replace).$htmlBouton;
    
    return $html;
  }
    
/*
    //on met a jour par rapport au portefeuille
    $this->setDroitsParPortefeuilles();
    
    use_helper('JavascriptBase'); //TODO: a terme virer l'utilisation du helper, pas possible tant que symfony
    
    $result = '';
    $strButton = '';
    $input = parent::render($extraAttributes, $replace);
    if ($this->edit && $this->getAttribute('disabled') == '') {
      $name = 'button_'.$this->getName();
      $id = 'button_'.$this->getId();
      $button = new mdWidgetButton($name, $this->libelle, array('id'=>$id));
      $url = sprintf('%s%sidop=%s&vop=', $this->url, (strpos($this->url, '?') == 0?'?':''), $this->getId());
      $strJavascript = sprintf('<script type="text/javascript">$("#%s").click(function(){openWindow("%s"+$("#%s").val())});</script>', $id, url_for($url), $this->getId()); //TODO: virer aussi le url_for
      $strButton = $button->render().$strJavascript;
    }
    $result = $input.$strButton."<span id=\"xt_{$this->attributes['id']}\">$extra</span>";
    return $result;
 * 
 */
}
?>
