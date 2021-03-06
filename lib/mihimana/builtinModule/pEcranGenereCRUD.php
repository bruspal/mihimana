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
@file : pEcranGenereCRUD.php
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
 * Description of pEcranGenereCRUD
 *
 * @author bruno
 */
class pEcranGenereCRUD extends mmProgProcedural{
  public function main($action, $request = null) {
    //On va creer le fichier source pour creer le CRUD lié a l'écran
    
    //Recuperation des parametres en parametres ou en session
    $nomEcran = $request->getParam('ecran', User::get('__editionNomEcran__', false));
    if ( ! $nomEcran)
    {
      $nomEcran = mmSession::get('__editionNomEcran__', false);
      if ( ! $nomEcran)
      {
        //ici on signal que c'est un appel de premiere main
        throw new mmExceptionControl("Aucun ecran de travail definie");
      }
    }
    else
    {
      User::set('__editionNomEcran__', $nomEcran);
    }

    //On genere le fichier source
    $nomFichier = APPLICATION_DIR.'/'.$nomEcran.'.php';
    if ( ! file_exists($nomFichier))
    {
      $fichierSource = fopen($nomFichier, 'w+');
      if ( ! $fichierSource)
      {
        throw new mmExceptionRessource("Impossible de créer le fichier $nomFichier");
      }

      $source = "<?php
class $nomEcran extends mdProgCRUD
{
  public function configure()
  {
    \$this->nomEcran = '$nomEcran';
  }
}
?>";
      if (fwrite($fichierSource, $source) === false) {
        throw new mmExceptionRessource("Erreur d'ecriture dans le fichier $nomFichier");
      }
      fclose($fichierSource);
      echo "<h1>Generation terminée</h1>";
    }
    else
    {
      echo mmErrorMessage('Le programme a déjà été généré. Generation annulé');
    }
  }
}

?>
