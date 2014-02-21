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
  @file : pLoginStd.php
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

class pLoginStd extends mmProg {

    public function executeIndex(mmRequest $request) {
        if (!mmUser::isAuthenticated()) {
            $this->executeLogin($request);
        } else {
            $this->redirect('?');
        }
    }

    public function executeLogin(mmRequest $request) {
        $this->initForm();
        if (count($_POST) > 0) {
            //C'est un retour
            try {
                $login = $request->getParam('login', false);
                if (!$login) {
                    throw new mmExceptionAuth('Veuillez saisir le login');
                }
                $password = $request->getParam('password', false);
                if (!$password) {
                    throw new mmExceptionAuth('Veuillez saisir le mot de passe');
                }
                //On effectue le login (il n'y a pas de reour, si jamais ca marche pas ca generera une exception
                mmUser::doLogin($login, $password);
                mmUser::flashSuccess('Bienvenue ' . mmUser::getNom());
                $this->redirect('?module=' . MODULE_DEFAUT . '&action=' . ACTION_DEFAUT);
            } catch (mmExceptionAuth $e) {
                mmUser::flashError($e->getMessage());
            }
        }
        //ici le html est generé implicitement
    }

    public function executeLogout(mmRequest $request) {
        mmUser::flashSuccess('Vous êtes déconnecté');
        mmUser::doLogout();
        $this->redirect('?module=' . MODULE_DEFAUT . '&action=' . ACTION_DEFAUT);
    }

    public function initForm() {
        $form = new mmForm();
        $form->setAction('?module=pLoginStd&action=login');
        $form->addWidget(new mmWidgetText('login'));
        $form->addWidget(new mmWidgetPassword('password'));
        $form->addWidget(new mmWidgetButtonSubmit());

        $this->form = $form;
    }

}

?>
