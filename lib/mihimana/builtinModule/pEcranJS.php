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
@file : pEcranJS.php
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


class pEcranJS extends mmProgCRUD {
  public function configure() {
    $this->tableName = 'EcranUtilisateur';
    $this->options['genereIndex'] = false;
    $this->setLayout('popup');
  }
    
  public function afficheFormulaire() {
    $this->form->setId('ecran_js_id');
    $this->form['script']->addAttribute('cols', 60);
    $this->form['script']->addAttribute('rows', 20);
    unset($this->form['precedent']);
    $this->form->addWidget(new mmWidgetButtonClose());
    echo $this->form->renderFormHeader();
    
    printf ('<fieldset><legend>Edition de script</legend>%s<br />%s</fieldset></form>', $this->form->renderButtons(), $this->form['script']);
    $js = $this->form->renderJavascript();
    echo $js;
  }
  
  public function initForm(\Doctrine_Record $table, $nouveau) {
    parent::initForm($table, $nouveau);
    $this->form->addWidget(new mmWidgetCodeMirror($this->form['script']));
  }
  
  public function postSave()
  {
    if (AJAX_REQUEST)
    {
      echo '<script type="text/javascript">$("#__mdDialog").jqmHide();</script>';
    }
    else
    {
      echo '<script type="text/javascript">window.close()</script>';
    }
    return false;
  }
}

?>
