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
        if ( ! $request->isEmpty()) {
            //C'est un retour
            try {
                $this->signinForm->setValues($request);
                $login = $request->get('login', false);
                $password = $request->get('password', false);
                if ($this->signinForm->isValid() && $login != false && $password != false) {
                    mmUser::doLogin($this->signinForm->getValue('login'), $this->signinForm->getValue('password'));
                    mmUser::flashSuccess('Bienvenue ' . mmUser::getNom());
                    $this->redirect('?module=' . MODULE_DEFAUT . '&action=' . ACTION_DEFAUT);
                }
            } catch (mmExceptionAuth $e) {
                mmUser::flashError($e->getMessage());
            }
        }
    }

    public function executeSubscribe(mmRequest $request) {
        $this->initForm();
        if ( ! $request->isEmpty()) {
            $this->registerForm->setValues($request);
            if ($this->registerForm->isValid()) {
                $login = $request->get('login', false);
                $password = $request->get('password', false);
                $this->registerSuccess = true;
//                try {
                    mmUser::createUser($login, $password);
//                }
                
            }
        }
    }

    public function executeLogout(mmRequest $request) {
        mmUser::flashSuccess('Vous êtes déconnecté');
        mmUser::doLogout();
        $this->redirect('?module=' . MODULE_DEFAUT . '&action=' . ACTION_DEFAUT);
    }

    public function initForm() {
        /* Signin form */
        switch (LOGIN_MODE) {
            case LOGIN_BY_EMAIL:
                $phLogin = 'email@example.com';
                break;
            case LOGIN_BY_USER:
                $phLogin = 'login';
                break;
            case LOGIN_BY_BOTH:
                $phLogin = 'email / login';
                break;
            default:
                throw new mmExceptionDev('LOGIN_MODE d&eacute;fini avec une valeur &eacute;ronn&eacute;e');
                break;
        }
        
        switch (REGISTER_MODE) {
            case REGISTER_BY_EMAIL:
                $phRegister = 'email@example.com';
                break;
            case REGISTER_BY_USER:
                $phRegister = 'login';
                break;
            default:
                throw new mmExceptionDev('REGISTER_MODE d&eacute;fini avec une valeur &eacute;ronn&eacute;e');
                break;
        }
        
        $signinForm = new mmForm();
        $signinForm->setAction('?module=pLoginStd&action=login');
        $signinForm->setId('loginForm');
        $signinForm->addWidget(new mmWidgetText('login', '', array('placeholder' => $phLogin)));
        $signinForm->addWidget(new mmWidgetPassword('password', '', array('placeholder' => 'password')));
        $signinForm->addWidget(new mmWidgetButtonSubmit('Sign In'));
        //validator
        $signinForm['login']->addValidation('notnull');
        if (REGISTER_MODE == REGISTER_BY_EMAIL) {
            $signinForm['login']->addValidation('email');
        }
        $signinForm['password']->addValidation('notnull');
        //make it availlable for template
        $this->signinForm = $signinForm;

        /* Register form */
        //formulaire d'enregistrement
        $registerForm = new mmForm();
        $registerForm->setId('subForm');
        $registerForm->setAction('?module=pLoginStd&action=subscribe');
        $registerForm->addWidget(new mmWidgetText('login', '', array('placeholder' => $phRegister)));
        $registerForm->addWidget(new mmWidgetText('loginConf', '', array('placeholder' => 'confirm '.$phRegister)));
        $registerForm->addWidget(new mmWidgetPassword('password', '', array('placeholder' => 'Password')));
        $registerForm->addWidget(new mmWidgetButtonSubmit("Register"));
        //validator
        $registerForm['login']->addValidation('notnull');
        if (REGISTER_MODE == REGISTER_BY_EMAIL) {
            $registerForm['login']->addValidation('email');
        }
        $registerForm['password']->addValidation('notnull');
        //make it availlable for template
        $this->registerForm = $registerForm;
    }

}
