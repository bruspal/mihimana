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
@file : pPdfSQL.php
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


class pPdfSQL extends mmProgProcedural
{
  public function principale($action = '', $parametres = null) {
    $optionsDefaut = array(
        'table'=>'ficged',
        'cle'=>'id',
        'donnees'=>'document',
        'download'=>false
    );
    $options = mmParseOptions($parametres->getParam('o', ''), $optionsDefaut);
    $valCle = $parametres->getParam('id', false);
    //On desactive 
    $this->setTemplate(false);
    $this->setLayout(false);
    $ficged = Doctrine_Core::getTable('Ged')->find($valCle); //a generalisé
    $dataImage = $ficged[$options['donnees']];
    $sizeImage = strlen($dataImage);
    header("Content-type: application/pdf");
    header("Content-Length: ".(string)$sizeImage);
    if ($options['download'])
    {
      header("Content-Disposition: attachment; filename=document.pdf"); 
      header("Content-Type: application/force-download" );    
      header("Content-Type: application/download" );
    }
    
    echo $dataImage;
  }
}
?>
