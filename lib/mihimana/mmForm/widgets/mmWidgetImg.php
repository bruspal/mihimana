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
@file : mmWidgetImg.php
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


class mmWidgetImg extends mmWidget
{
  protected
          $url,
          $largeur,
          $hauteur;
  public function __construct($name, $url, $options) {
    parent::__construct($name);
    $this->url = $url;
    $this->largeur = $options->get('largeur', '');
    $this->hauteur = $options->get('hauteur', '');
  }
  
  public function render($extraAttributes = array(), $replace = false) {
    if ($this->rendered == true) return '';
    if ($this->largeur != '')
    {
      $largeur = ' width="'.$this->largeur.'"';
    }
    else
    {
      $largeur = '';
    }
    if ($this->hauteur != '')
    {
      $hauteur = ' height="'.$this->hauteur.'"';
    }
    else
    {
      $hauteur = '';
    }
    $result = sprintf('<img src="%s"%s%s onclick="openWindow  (\'%s\')" />', $this->url, $hauteur, $largeur, $this->url);
    $this->rendered = true;
    return $result.$this->renderHelp().$this->renderAdminMenu();
  }
  
  public function renderPdf($extraAttributes = array(), $replace = false) {
    if ($this->rendered == true) return '';
    if ($this->largeur != '')
    {
      $largeur = ' width="'.$this->largeur.'"';
    }
    else
    {
      $largeur = '';
    }
    if ($this->hauteur != '')
    {
      $hauteur = ' height="'.$this->hauteur.'"';
    }
    else
    {
      $hauteur = '';
    }
    $result = sprintf('<img src="%s"%s%s />', $this->url, $hauteur, $largeur);
    $this->rendered = true;
    return $result;
  }
}

?>
