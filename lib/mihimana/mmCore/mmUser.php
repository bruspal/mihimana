<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmCore
  @file : mmUser.php
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

/**
 * Classe permettant de stocker des information propore a l'utilisateur connecté, comme par exemple sont identifiant, les messages derreur ou d'information qui lui sont destiné et etc. 
 */
class mmUser extends mmSession {

    //Ensemble des methode de gestion du context
    public
            $context,
            $nomContext;

    /*
     * Procedure de gestion du contexte (sera generalisé par la suite
     */

    public function __construct($nomContext = false, $forceVide = false) {
        parent::__construct();
        if ($nomContext != false) {
            //On a fournis un nom de context, on charge
            $this->chargeContext($nomContext, $forceVide);
        }
    }

    protected function __chargeContext($nomContext, $forceVide = false) {
        $this->nomContext = $nomContext;
        if ($forceVide) {
            
        }
        $context = mmUser::get($nomContext, array());
    }

    protected function __sauveContext() {
        
    }

    /*
     * Methodes de communication a  vec l'uilisateur
     */

    /*
     * Methode de gestion de la pile des messages
     */

    /**
     * Ajoute un message utilisateur dans la pile des messages identifié par message
     * 
     * @param type $type
     * @param type $message 
     */
    public static function flashMessage($type, $message) {
        $__flashs__ = mmSession::get('__flashs__', array());
        $cleUniqueMessage = hash('adler32', $message);
        $__flashs__[$type][$cleUniqueMessage] = $message;
        mmSession::set('__flashs__', $__flashs__);
    }

    public static function clearFlashes() {
        mmSession::set('__flashs__', array());
    }

    /**
     * Empile un message d'erreur
     * @param type $message 
     */
    public static function flashError($message) {
        self::flashMessage('error', $message);
    }

    /**
     * Emile un message de warning
     * @param type $message 
     */
    public static function flashWarning($message) {
        self::flashMessage('warning', $message);
    }

    /**
     * Empile un message de succes
     * @param type $message 
     */
    public static function flashSuccess($message) {
        self::flashMessage('success', $message);
    }

    /**
     * Empile un message d'information
     * @param type $message 
     */
    public static function flashInfo($message) {
        self::flashMessage('info', $message);
    }

    /**
     * Empile un message destiné uniquement aux superadministrateur.
     * @param type $message 
     */
    public static function flashSuperAdmin($message) {
        if (self::superAdmin()) {
            self::flashMessage('admin', $message);
        }
    }

    /**
     * Empile un message qui n'apparaitra que si l'application est en mode debug
     * @param type $message 
     */
    public static function flashDebug($message) {
        if (DEBUG) {
            self::flashMessage('debug', $message);
        }
    }

    /**
     * retourne le tableau des flashs. Si $type est omis, on retour le tableau entier (un tableau de tableau).<br />
     * Si on donne le type: retourne que le tableau de ce type.<br />
     * Si le type n'existe pas ou que le tableau des flash est vide on renvois un tableau vide
     * @param type $type
     * @return array() 
     */
    public static function getFlashs($type = false) {
        if ($type) {
            $__flashs__ = mmSession::get('__flashs__', array());
            if (isset($__flashs__[$type])) {
                return $__flashs__[$type];
            } else {
                return array();
            }
        } else {
            return mmSession::get('__flashs__', array());
        }
    }

    /**
     * Genere le code HTML pour afficher les flash a envoyer a l'utilisateur. si videApresRendu est a vrai (defaut) la pile des flash est vidée.
     * @param type $type
     * @param type $videApresRendu
     * @return type 
     */
    public static function renderFlashs($type = false, $videApresRendu = true) {
        //On construit la chaine HTML
        if ($type) {
            //on veux explicitement un type de flash
            $__flashs__ = self::getFlashs($type);
            $resultat = self::parcoursFlashs($__flashs__, $type);
        } else {
            //On veux recuperer tous les flashs
            $__flashs__ = self::getFlashs();
            $resultat = self::parcoursFlashs($__flashs__);
        }
        //Si c'est demané on vide la pile des flashs
        if ($videApresRendu) {
            mmSession::remove('__flashs__');
        }
        //on renvois la chaine contenant le HTML
        return $resultat;
    }

    /**
     * Genere le HTML des flash. Si $type est omis genere le HTML pour tous les flashs
     * @param type $donneesFlashs une pile de flash
     * @param type $type
     * @return type 
     */
    protected static function parcoursFlashs($donneesFlashs, $type = false) {
        $resultat = '';
        foreach ($donneesFlashs as $nomValeurs => $valeur) {
            if (is_array($valeur)) {
                //La valeur est un tableau. dans ce cas la c'est une collection de flashs dans $valeur
                //On traite le tableau stocké dans $valeur
                $resultat .= self::parcoursFlashs($valeur, $nomValeurs);
            } else {
                //c'est un simple message d'erreur ?
                //on effectue le rendu
                $resultat .= sprintf('<div class="flash_%s">%s</div>', $type, $valeur);
            }
        }
        //on renvois le resultat
        return $resultat;
    }

    /*
     * Methode d'information sur l'utilisateur connecté
     * 
     */

    public static function getNom($full = false) {
        $user = self::get('__user__', false);
        if ($full) {
            return ($user['firstname'] . ' ' . $user['lastname']);
        }
        return ($user['firstname']);
    }

    /**
     * Retourne TRUE si l'utilisateur connecté est un super administrateur
     * @return boolean 
     */
    public static function superAdmin() {
        if (MODE_INSTALL) {
            return true;
        }
        if (NO_LOGIN) {
            return SUPER_ADMIN;
        }
        $user = self::get('__user__', false);
        if ($user !== false && isset($user['super_admin']) && $user['super_admin'] == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retourne TRUE si l'itilisateur courant est authentifié
     * @param string $module Module sur lequel effectué la vérification
     * @param string $action Action sur laquelle faire la vérification
     * @return boolean 
     */
    public static function isAuthenticated($module, $action) {
        if (MODE_INSTALL || NO_LOGIN) {
            // On est en mode installation ou en mode fonctionnement sans login
            // On cree en mémoire un utilisateur fictif avec des parametres figé
            if (!MODE_INSTALL) {
                $user = new User();
                $user['id'] = 1;
                $user['actif'] = 1;
                if (MODE_INSTALL) {
                    $user['super_admin'] = true;
                    $user['firstname'] = 'INSTALL';
                } else {
                    $user['super_admin'] = SUPER_ADMIN;
                    if (SUPER_ADMIN) {
                        $user['firstname'] = 'ADMINISTRATEUR';
                        $user['username'] = "L'administrateur d'install";
                    } else {
                        $user['firstname'] = 'Visiteur';
                        $user['username'] = '';
                    }
                }
                $user['email'] = '';
                $user = $user->toArray();
                $user['auth'] = true;
                self::set('__user__', $user);
            } else {
                self::remove('__user__');
            }
            return true;
        }
        //Verification d'authentification normale'
        $user = self::get('__user__', false);
        if ($user !== false && isset($user['auth']) && $user['auth'] == true) {
            return true;
        } else {
            //Vérification des module/actions auquelles on a acces sans être identifier
            //pour test
            require APPLICATION_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'credentials.php';
            $strCredentials = $module . '/' . $action;
            if (isset($credentials[$strCredentials]) && $credentials[$strCredentials] === false) { // on a le droit d'acceder a ce module/action de manière anonyme
                $user = self::createVirtualGuest();
                $user = $user->toArray();
                self::set('__user__', $user);
                return true; //ok on est identifié
            }
            $strCredentials = $module;
            if (isset($credentials[$strCredentials]) && $credentials[$strCredentials] === false) { // on a le droit d'acceder a ce module/action de manière anonyme
                $user = self::createVirtualGuest();
                $user = $user->toArray();
                self::set('__user__', $user);
                return true; //ok on est identifié
            }
            //si on a pas le droit de venir de manière anonyme ici on se fait jeter
            return false;
        }
    }

    private static function createVirtualGuest() {
        $user = new User();
        $user['id'] = false;
        $user['login'] = 'Guest';
        $user['password'] = false;
        $user['actif'] = true;
        $user['super_admin'] = false;
        $user['lastname'] = 'Visiteur';
        $user['firstname'] = 'Visiteur';
        $user['username'] = 'Invité';

        return $user;
    }

    /**
     * Perform login mechanism
     * @param type $login
     * @param type $password
     * @return boolean
     * @throws mmExceptionAuth
     */
    public static function doLogin($login, $password) {
        switch (LOGIN_MODE) {
            case LOGIN_BY_USER:
                $user = Doctrine_Core::getTable('User')->createQuery()->
                        where('login = ? AND actif=1', $login)->
                        fetchOne();
                break;
            case LOGIN_BY_EMAIL:
                $user = Doctrine_Core::getTable('User')->createQuery()->
                        where('email = ? AND actif=1', $login)->
                        fetchOne();
                break;
            case LOGIN_BY_BOTH:
                $user = Doctrine_Core::getTable('User')->createQuery()->
                        where('login = ? AND actif=1', $login)->
                        fetchOne();
                if (!$user) {
                    $user = Doctrine_Core::getTable('User')->createQuery()->
                            where('email = ? AND actif=1', $login)->
                            fetchOne();
                }
                break;
            default:
                throw new mmExceptionConfig('LOGIN_MODE not correctly set, check config.php');
                break;
        }
        if ($user) {
            // On a un enregistrement, on verifie le mot de passe
            $user['last_login'] = date("Y-m-d H:i:s");
            $user['remote_ip'] = $_SERVER['REMOTE_ADDR'];
            if (!$user['password']) {
                self::remove('__user__');
                throw new mmExceptionAuth('utilisateur invalide');
            }
            if ($user['password'] == self::encryptPassword($password, $user['salt'])) {
                //c'est ok l'utilisateur est reconnu
                // on met a jour les info de suivie de connection
                $user['login_failure'] = 0; //on remet a zero le compteur d'echec de connexion
                $user->save();
                // mise a jour de la session
                $user = $user->toArray();
                $user['auth'] = true;
                self::set('__user__', $user);
                return true;
            } else {
                $user['login_failure'] = $user['login_failure'] + 1; //echec de connection on additionne 1 au nombre de tentative de connection raté
                $user->save();
                self::remove('__user__');
                throw new mmExceptionAuth("Utilisateur inconnu ou inactif");
            }
        } else {
            self::remove('__user__');
            throw new mmExceptionAuth("Utilisateur inconnu ou inactif");
        }
    }

    /**
     * Create a new user
     * @param string $login username or email adresse depending the LOGIN_MODE parameter
     * @param string $password uncrypted password
     * @param boolean $actif set to TRUE (default) to make user able to log in
     * @param boolean $superAdmin set to TRUE to make user as a super admin, FALSE by default
     * @param string $email user email
     */
    public static function createUser($login, $password, $actif = true, $superAdmin = false, $email = '') {
        //verification que le parametrage est correct
        if (LOGIN_MODE < 3 && LOGIN_MODE != REGISTER_MODE) {
            throw new mmExceptionConfig("Incohérence dans la configuration de l'enregistrement/login.Si MODE n'est pas LOGIN_BY_BOTH, LOGIN_BY et REGISTER_BY doivent utilisé les meme mode");
        }
        $user = new User();
        if (REGISTER_MODE == REGISTER_BY_USER) {
            $user['login'] = $login;
            $user['email'] = $email;
        } else {
            $user['login'] = $login;
            $user['email'] = $login;
        }
        mt_srand();
        $salt = md5(uniqid(md5(mt_rand() . microtime()), true));
        $encryptedPassword = self::encryptPassword($password, $salt);
        $user['salt'] = $salt;
        $user['password'] = $encryptedPassword;
        $user['actif'] = $actif;
        $user['super_admin'] = $superAdmin;
        $user['registration_date'] = date("Y-m-d H:i:s");
        $user->save();
    }

    public static function doLogout() {
        self::remove('__user__');
    }

    public static function encryptPassword($password, $salt) {
        $cryptedPassword = crypt($password . $salt, $salt) . crypt(md5($salt . $password), $salt);
        return $cryptedPassword;
    }

}