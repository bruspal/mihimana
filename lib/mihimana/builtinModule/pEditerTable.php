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
@file : pEditerTable.php
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


class pEditerTable extends mmProgCRUD
{
  public function configure()
  {
    //on defini sur quelle table on travail
    $nomTable = $this->parametresProgramme->getParam('table', false); // en parametre ?
    if ( ! $nomTable)
    {
      $nomTable = mmSession::get('nomTablePEditerTable', false);
      if ( ! $nomTable)
      {
        throw new mmExceptionControl("Impossible d'éxécuter le programme d'edition des tables sans donner de nom de table en parametre");
      }
    }
    else
    {
      $nomTable = mmSession::set('nomTablePEditerTable', $nomTable);
    }
    
    $this->tableName = $nomTable;
  }
  
//  public function executeIndex(mdRequest $request) {
//    //Doctrine::getLoadedModels();
//    //echo '<h1>Viendra la liste des tables</h1>';
//    parent::executeIndex($request);
//  }
}
?>
