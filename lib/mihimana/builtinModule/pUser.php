<?php

/* ------------------------------------------------------------------------------
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
  ------------------------------------------------------------------------------ */



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pUser
 *
 * @author bruno
 */
class pUser extends mmProgCRUD {

    public function configure(mmRequest $request) {
        $this->verifDroits();
        $this->tableName = 'User'; //Table sur laquel on travail
        $this->options['autoHtml'] = false; //ne genere pas les formulaires
        $this->options['cols'] = array('login', 'lastname', 'firstname'); //colonnes apparaissant dans la liste
        $this->options['addDelete'] = true;
    }

    public function save(\mmRequest $request) {

        // before saving password encryption
        if ( ! ($user = $request->get('user', false))) {
            throw new mmExceptionControl('No data receive from user manager');
        }
        if (empty($user['password'])) {
            throw new mmExceptionControl('No password provided for user');
        }
        $password = $user['password'];
        $salt = md5(uniqid(md5(mt_rand() . microtime()), true));
        $user['password'] = mmUser::encryptPassword($password, $salt);
        $user['salt'] = $salt;
        //update request with modified data
        $request->set('user', $user);
        //go back to normal behavior
        parent::save($request);
    }

    public function initForm(\Doctrine_Record $table, $nouveau) {
        //standardised creation
        parent::initForm($table, $nouveau);
        // customization
        $this->form->addWidget(new mmWidgetSelect($this->form['actif'], array('0'=>'No', '1'=>'Yes')));
        $this->form->addWidget(new mmWidgetSelect($this->form['super_admin'], array('0'=>'No', '1'=>'Yes')));
        $this->form['password'] = '';

    }

    public function verifDroits() {
        if (!mmUser::superAdmin()) {
            mmUser::flashError('Vous ne pouvez pas acceder à cet écran');
            $this->redirect('@homepage');
        }
    }

}