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
  @file : mmWidgetTextSearch.php
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

class mmWidgetTextSearch extends mmWidget {

    protected $action;

    public function __construct($name, $value = '', $attributes = array()) {
        $this->addAttribute('autocomplete', 'off');
        $this->addAttribute('class', 'text');
        parent::__construct($name, 'text', $value, $attributes);
    }

    public function postAddWidget() {
        //On ajoute le bout de javascript
        if ($this->containerForm != null) {
            $nomTable = $this->containerForm->getTableName();
            if ($nomTable != '') {
                $idHtml = $this->getId();
                if (MODULE_COURANT == MODULE_DEFAUT) {
                    $urlBase = '?';
                } else {
                    $urlBase = '?module=' . MODULE_COURANT;
                }
                $nomChamp = $this->getName();
                if ($this->containerForm->isNew()) {
                    $else = '';
                } else {
                    $else = "$('#$idHtml').val($nomChamp);mdPopup('Enregistrement inexistant')";
                }
                $script = "
        var $nomChamp = '';
        $('#$idHtml').focus(function(){
          $nomChamp = $(this).val();
        });
        $('#$idHtml').change(function(){
          $.ajax({
            url: '?',
            data: {
              module: 'pWs',
              action: 'cpe',
              t: '$nomTable',
              c: '$nomChamp',
              v: $(this).val(),
              s: 1
            },
            success: function(retoursw){
              if(retoursw.success == true)
              {
                if(retoursw.data != false)
                {
                  goPage('{$urlBase}&b='+retoursw.data.id);
                }
                else
                {
                  $else
                }
              }
              else
              {
                mdPopup(retoursw.message);
              }
            }
          });
        });";
                $this->addJavascript('recherche', $script);
            }
        }
    }

}