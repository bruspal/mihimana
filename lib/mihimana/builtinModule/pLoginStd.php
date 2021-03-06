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

    /**
     * Do http login
     * @param mmRequest $request
     * @throws mmExceptionConfig
     * @throws mmExceptionDev
     */
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
                    $this->redirect(url('@home'));
                }
            } catch (mmExceptionAuth $e) {
                mmUser::flashError($e->getMessage());
            }
        }
    }

    /**
     * do json login
     * @param mmRequest $request
     * @throws mmExceptionConfig
     * @throws mmExceptionHttp
     */
    public function executeJSONLogin(mmRequest $request) {
        //pure json output
        $this->setTemplate(false); //no template
        $this->outputAsJson(); // no layout + JSON header

        if (AJAX_REQUEST || DEBUG) { //Access authorized only through ajax request (if debug is true http acces allowed too).
            $data = mmJSON::getPost();
            if (empty($data) || empty($data['login']) || empty($data['password'])) {
                mmJSON::sendBadRequest();
                return false;
            }
            try {
                mmUser::doLogin($data['login'], $data['password']);
                $dataUser = mmUser::get('__user__');
                mmJSON::sendJSON($this->cleanupUserData($dataUser));
            } catch (mmExceptionAuth $ex) {
                mmJSON::sendUnauthorized();
            }
        } else {
            throw new mmExceptionHttp(mmExceptionHttp::FORBIDDEN);
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
        $this->redirect(url('login'));
    }

    public function executeJSONLogout(mmRequest $request) {
        //Pure json output
        $this->setTemplate(false); //no template
        $this->outputAsJson(); // no layout + JSON header

        if (AJAX_REQUEST || DEBUG) { //Access authorized only through ajax request (if debug is true http acces allowed too).
            mmUser::doLogout(); //logout
            mmJSON::sendJSON(); // send success
        } else {
            throw new mmExceptionHttp(mmExceptionHttp::FORBIDDEN);
        }
    }

    public function executeResetPassword(mmRequest $request) {

    }

    public function executeJSONResetPassword(mmRequest $request) {

    }


    public function executeJSONIsLogged(mmRequest $request) {
        //set pure json mode : turns off template and layout set header
        $this->setTemplate(false);
        $this->outputAsJson();
        if (mmUser::isAuthenticated()) {
            $user = mmUser::getInstance();
            if ($user) {
                $user = $user->toArray();
                mmJSON::sendJSON($this->cleanupUserData($user));
            } else {
                mmStatusNotFound();
                mmJSON::sendNotFound('Unknown user');
            }
        } else {
            mmStatusUnauthorized();
            mmJSON::sendUnauthorized();
        }
    }

    /**
     * init login form object
     * @throws mmExceptionDev
     * @throws mmExceptionForm
     */
    protected function initForm() {
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
        $signinForm->setAction(url('login'));
        $signinForm->setId('loginForm');
        $signinForm->addWidget(new mmWidgetText('login', '', array('placeholder' => $phLogin)));
        $signinForm->addWidget(new mmWidgetPassword('password', '', array('placeholder' => 'password')));
        $signinForm->addWidget(new mmWidgetButtonSubmit('Sign In'));
        //validator
        $signinForm->addValidator('login', 'notnull');
        if (REGISTER_MODE == REGISTER_BY_EMAIL) {
            $signinForm->addValidator('login', 'email');
        }
        $signinForm->addValidator('password', 'notnull');
        //make it availlable for template
        $this->signinForm = $signinForm;

        /* Register form */
        //formulaire d'enregistrement
        $registerForm = new mmForm();
        $registerForm->setId('subForm');
        $registerForm->setAction(url('subscribe'));
        $registerForm->addWidget(new mmWidgetText('login', '', array('placeholder' => $phRegister)));
        $registerForm->addWidget(new mmWidgetText('loginConf', '', array('placeholder' => 'confirm '.$phRegister)));
        $registerForm->addWidget(new mmWidgetPassword('password', '', array('placeholder' => 'Password')));
        $registerForm->addWidget(new mmWidgetButtonSubmit("Register"));
        //validator
        $registerForm->addValidator('login', 'notnull');
        if (REGISTER_MODE == REGISTER_BY_EMAIL) {
            $registerForm->addValidator('login', 'email');
        }
        $registerForm->addValidator('password', 'notnull');
        //make it availlable for template
        $this->registerForm = $registerForm;
    }

    /*
     * Protected methods
     */
    /**
     * This methode remove critical data from the user array to avoid security issues
     * @param array $user user data to be cleaned
     * @return array cleaned data
     */
    protected function cleanupUserData($user) {
        unset($user['password']);
        unset($user['salt']);

        return $user;
    }
}
