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
  @file : mmWidgetTinyMce.php
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

class mmWidgetTinyMce extends mmWidget {

    public function __construct($name, $value = '', $attributes = array()) {
        $this->addAttribute('autocomplete', 'off');
        $this->addAttribute('class', 'textarea');
        parent::__construct($name, 'text', $value, $attributes);
        $script = '$(document).ready(function(){
  tinyMCE.init({
    mode: "exact",
    elements: "' . $this->getId() . '",
    relative_url: true,
    convert_fonts_to_span: true,
    width: "100%",
    body_class: "content",
//    convert_newlines_to_brs : true,
    font_size_style_values : "8pt,10pt,12pt,14pt,18pt,24pt,36pt",
    force_br_newlines : true,
    force_p_newlines : false,
    forced_root_block : "",     
    language: "fr",
    preformatted : true,
    plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
    theme: "advanced",
    theme_advanced_buttons1 : "save,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
//    theme_advanced_buttons1 : "save,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
    theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,image,cleanup,help,code,|,preview,|,forecolor,backcolor",
    theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,advhr,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
//    theme_advanced_buttons4 : "styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "bottom",
    theme_advanced_resizing : true
  });
});';

        $this->addJavascript('__runTinyMce__', $script);
    }

    public function render($extraAttributes = array(), $replace = false) {

        //on met a jour par rapport au portefeuille
        $this->setDroitsParPortefeuilles();

        //Pour la futur gestion des droits
        // si ecriture, edition, visu, delete
        // Pour le moment on fais rien de particulier
        $this->rendered = true;
        if ($this->edit && $this->enabled) {
            $this->addResultClass();
            return sprintf('<textarea type="%s" name="%s" %s>%s</textarea>', $this->attributes['type'], sprintf($this->nameFormat, $this->attributes['name']), $this->generateAttributes($extraAttributes, $replace), $this->attributes['value']) . $this->renderHelp() . $this->renderAdminMenu();
            ;
        } else {
            if ($this->view || ($this->edit && !$this->enabled)) {
                return sprintf('<span %s>&nbsp;</span>', $this->generateAttributes($extraAttributes, $replace)) . $this->renderAdminMenu();
                ;
            } else {
                return '';
            }
        }
    }

}