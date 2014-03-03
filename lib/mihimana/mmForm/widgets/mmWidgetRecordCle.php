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
  @file : mmWidgetRecordCle.php
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

class mmWidgetRecordCle extends mmWidgetText {

    protected $options;

    public function __construct($name, $value = '', $options = '', $attributes = array()) {
        if (is_string($options)) {
            $optionsDefaut = array(
                'table' => '',
                'cle' => '',
                'actionListe' => "?b=%s",
                'actionNouveau' => "?b=-1",
                'cols' => '',
                'auto' => 0
            );
            $this->options = new mmOptions($options, $optionsDefaut);
        } else {
            $this->options = $options;
        }
        parent::__construct($name, $value, $attributes);
    }

    public function render($extraAttributes = array(), $replace = false) {
        //on genere le html de la zone de saisie
        $nomWidget = $this->getName();
        $chaineOptions = $this->options->getChaine();
        //Generation de la zone de saisie, on appel le parent
        $html = parent::render($extraAttributes, $replace);
        //On ajoute les elements
        $scriptCherche = sprintf("mdAjaxSubWindow('%s_wid', '?module=pWs&action=ccl&o=%s;vi='+$('#%s').val())", $nomWidget, $chaineOptions, $this->getId());
        $scriptNv = "goPage('{$this->options['actionNouveau']}')";

        //on ajoute les widget de controle
        $boutonClose = sprintf('<input type="button" value="Fermer/annuler" onclick="$(\'#%s\').val(\'%s\');$(\'#%s_wid\').hide()" />', $this->getId(), $this->getValue(), $nomWidget);
        $html .= sprintf('<div id="%s_wid" class="mdWidgetSub" style="position: absolute; display: none;"><div class="subContent"></div><div>%s</div></div>', $nomWidget, $boutonClose);
        if ($this->options['auto'] == 1) {
            $html .= "<script type=\"text/javascript\">$('#" . $this->getId() . "').change(function(){" . $scriptCherche . "})</script>";
        } else {
            $btRecherche = new mmWidgetButton($nomWidget . "_btr", 'V', array('onclick' => $scriptCherche));
            $btNouveau = new mmWidgetButton($nomWidget . "_btn", 'Nouveau', array('onclick' => $scriptNv));
            $html .= $btRecherche->render();
            $html .= $btNouveau->render();
        }

        return $html;
    }

}