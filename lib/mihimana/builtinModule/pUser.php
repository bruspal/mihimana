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
@file : pUser.php
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



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pUser
 *
 * @author bruno
 */
class pUser extends mmProgCRUD
{
  public function configure(mmRequest $request) {
    $this->verifDroits();
    $this->tableName = 'Utilisateur'; //Table sur laquel on travail
    $this->options['autoHtml'] = false; //ne genere pas les formulaires
    $this->options['cols'] = array('login', 'password', 'nom', 'prenom'); //colonnes apparaissant dans la liste
  }
  
  public function verifDroits()
  {
    if ( ! mmUser::superAdmin())
    {
      mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
      $this->redirect('?');
    }
  }
}

?>
