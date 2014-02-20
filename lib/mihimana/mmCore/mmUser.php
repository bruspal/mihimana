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
            return ($user['prenom'] . ' ' . $user['nom']);
        }
        return ($user['prenom']);
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
     * @return boolean 
     */
    public static function isAuthenticated() {
        if (MODE_INSTALL || NO_LOGIN) {
//      //On cree en mémoire un utilisateur fictif avec des parametres figé
//      $user = new Utilisateur();
//      $user['id'] = 1;
//      $user['actif'] = 1;
//      if (MODE_INSTALL)
//      {
//        $user['super_admin'] = 1;
//      }
//      else
//      {
//        $user['super_admin'] = SUPER_ADMIN;
//      }
//      self::set('__user__', $user);
            return true;
        }
        $user = self::get('__user__', false);
        if ($user !== false && isset($user['auth']) && $user['auth'] == true) {
            return true;
        } else {
            return false;
        }
    }

    public static function doLogin($login, $password) {
        $user = Doctrine_Core::getTable('Utilisateur')->createQuery()->
                where('login = ? AND actif=1', $login)->
                fetchOne();
        if ($user) {
            // On a un enregistrement, on verifie le mot de passe
            $user = $user->toArray();
            if (!$user['password']) {
                self::remove('__user__');
                throw new mmExceptionAuth('utilisateur invalide');
            }
            if ($user['password'] == $password) {
                //c'est ok l'utilisateur est reconnu
                $user['auth'] = true;
                self::set('__user__', $user);
                return true;
            } else {
                self::remove('__user__');
                throw new mmExceptionAuth("Utilisateur inconnu ou inactif");
            }
        } else {
            self::remove('__user__');
            throw new mmExceptionAuth("Utilisateur inconnu ou inactif");
        }
    }

    public static function doLogout() {
        self::remove('__user__');
    }

}

?>
