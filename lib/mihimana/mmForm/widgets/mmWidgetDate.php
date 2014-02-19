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
@file : mmWidgetDate.php
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


class mmWidgetDate extends mmWidgetText {
  
  public function __construct($name, $value = '', $attributes = array()) {
    $this->addAttribute('size', 10);
    parent::__construct($name, $value, $attributes);
    $this->addCssClass('stddate');
    $this->addJavascript('checkDate', sprintf("mdJsCheckDate($('#%s'));\n", $this->attributes['id']));
  }
  
  public function clean() {
    //On transforme la valeur du formulaire en valeur valable pour la base
    parent::clean();
    //On 'retourne' la date pour la rendre compatible avec la base de donnee
    $value = $this->attributes['value'];
    $backValue = $value;
    if ($value != '') {
      //Elimination des caractere separateur
      $value = str_replace(array('-','/',' '),'', $value);
      
      $length = strlen($value);
      if ($length < 6) {
        $error = 'La date doit etre au format "JJ-MM-[AA]AA" ou "JJMM[AA]AA';
        $this->errors[] = $error;
        $this->dbValue = null;
        throw new mmExceptionWidget($error);
      }
      //decoupage en jour, mois , annee
      $d=(integer) substr($value, 0, 2);
      $m=(integer) substr($value, 2, 2);
      $Y=substr($value, 4);
      $lY = strlen($Y);
      if ($lY == 3 || $lY > 4) {
        $error = 'L\'annee doit etre au format [AA]AA';
        $this->errors[] = $error;
        $this->dbValue = null;
        throw new mmExceptionWidget($error);
      }
      //pour l'annee si seulement deux digit ca va forcement valoir entre 0 et 99
      //si < a 30 on est dans les 2000
      //sinon 1900
      if ($Y < 30) {
        $Y += 2000;
      }
      elseif ($Y < 99) {
        $Y += 1900;
      }
      //Verification des jour et des mois
      if ($m < 1 || $m > 12) {
        $error = "Le mois doit etre compris entre 01 et 12";
        $this->errors[] = $error;
        $this->dbValue = null;
        throw new mmExceptionWidget($error);
      }
      //On ramene les 29 fevrier au 28
      if ($d == 29 && $m == 2) {
        $d--;
      }
      
      $nbrJours = date("t", mktime(0, 0, 0, $m, 1, $Y));
      if ($d < 1 || $d > $nbrJours) {
        $error = "Le jour doit etre compris entre 01 et $nbrJours";
        $this->errors[] = $error;
        $this->dbValue = null;
        throw new mmExceptionWidget($error);
      }
      //creation de la chaine de date
      $this->dbValue = sprintf("%04d-%02d-%02d", $Y, $m, $d);
      //formatage de la valeur
      $this->attributes['value'] = sprintf("%02d-%02d-%04d", $d, $m, $Y);
    }
  }
  
  public function dbClean() {
    // rend human readable depuis la base
    if ( ! $this->dbValue || $this->dbValue == '0000-00-00') {
      $this->attributes['value'] = '';
    }
    else {
      list($an, $mois, $jour) = explode('-', $this->dbValue);
      $this->attributes['value'] = sprintf("%s-%s-%s", $jour, $mois, $an);
    }
  }
  
}
?>
