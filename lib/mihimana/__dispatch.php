<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: root
@file : __dispatch.php
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
 * Dispatching
 */
try
  { //On protege contre les erreurs ce qui se trouve dans le try { }
  //detection d'un appel AJAX
  if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
  {
    define('AJAX_REQUEST', true);
  }
  else
  {
    define('AJAX_REQUEST', false);
  }

  //Recuperation des parametres fournis en URL et parametrage pour l'execution des modules
  $request = new mmRequest();
  
  $module = $request->getParam('module', MODULE_DEFAUT);
  $action = $request->getParam('action', ACTION_DEFAUT);
  
  //verification de l'authentification
  if ( ! mmUser::isAuthenticated())
  {
    //Si on est en ajax on reviens en standard
    if (AJAX_REQUEST)
    {
      echo mdAjaxError('La session à expirée veuillez vous reconnecter<br /><button onclick="goPage(\'?\')">Reconnection</button>');
      die;
    }
    //on est pas identifié on va vers le login
    $module = 'pLogin';
    $action = 'login';
  }
  
  //detection si c'est un appel en clair ou du https
  if ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
  {
    define('HTTPS', true);
  }
  else
  {
    define('HTTPS', false);
  }
  //On declare les module et action courant dans des constante pour pouvoir les recuperer facilement dans les modules et scripts.
  define('MODULE_COURANT', $module);
  define('ACTION_COURANTE', $action);

  
  //ici deux choix : soit le fichier php existe. On le charge et l'execute. Sinon on charge et execute le programme générique d'extension
  $fichierProgramme = false;
  if (file_exists('../'.APPLICATION.'/'.$module.'.php'))
  {
    $fichierProgramme = '../'.APPLICATION.'/'.$module.'.php';
    define('PROGRAMME_STANDARD', false);
  }
  else
  {
    if (file_exists(MAIDES_DIR.'/programmesStandard/'.$module.'.php'))
    {
      $fichierProgramme = MAIDES_DIR.'/programmesStandard/'.$module.'.php';
      define('PROGRAMME_STANDARD', true);
    }
  }
  
//  if (file_exists('../'.APPLICATION.'/'.$module.'.php'))
  if ($fichierProgramme)
  {
    //fichier PHP personnalisé
    //inclusion du programme en fonction du module
//    require '../'.APPLICATION.'/'.$module.'.php';
    require $fichierProgramme;
    //creation en memoire du programme
    $prog = new $module();
    //on execute l'action du programme avec les parametres fournis au script par l'url
    $prog->execute($action, $request);
  }
  else
  {
    throw new mmExceptionControl("<h1>Module inexistant</h1>");
  }
  
}
catch (mmExceptionControl $e) {
  
  $sortieProgramme = '<h1>'.$e->getMessage().'</h1>';
  include APPLICATION_DIR.'/templates/layout.php';
}
catch (Exception $e) {
  //Si une erreur non gerée se produit on affiche le message d'erreur detaillé si on est en mode DEBUG, sinon on fais autre chose (genre log, mail, etc)
  //TODO: voir comment gerer les erreurs critique en production
  $sortieProgramme = ob_get_clean();
  if (DEBUG)
  {
  //En mode debug ? On affiche les informations détaillées sur l'erreur
    $sortieProgramme .="<fieldset><legend>Erreur</legend>".$e->getMessage()."</fieldset>
    <fieldset><legend>Trace</legend><pre>".$e->getTraceAsString()."</pre>
    </fieldset>";
  }
  else
  {
    //En mode production on affiche une erreur generaliste
    $sortieProgramme = "<h1>Une erreur critique a eu lieu. Veuillez contacter le service informatique.</h1>";
  }
  include APPLICATION_DIR.'/templates/layout.php';
}
?>
