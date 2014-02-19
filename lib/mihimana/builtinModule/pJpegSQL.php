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
@file : pJpegSQL.php
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


class pJpegSQL extends mmProgProcedural
{
  public function principale($action = '', $parametres = null) {
    $optionsDefaut = array(
        'table'=>'ficged',        //table dans laquel se trouve le champ
        'cle'=>'id',              //cle unique de l'enregistrement
        'donnees'=>'document',    //champ contenant les données de l'image
        'dim'=>false,             //dimension de l'image. Si omis renvois l'image telquel sinon effectue un redimenssionement a la volée
        'download'=>false         //a true force le download du fichier. sinon laise la responsabilité au fichier
    );
    $options = mmParseOptions($parametres->getParam('o', ''), $optionsDefaut);
    $valCle = $parametres->getParam('id', false);
    //On desactive 
    $this->setTemplate(false);
    $this->setLayout(false);
    $ficged = Doctrine_Core::getTable('Ged')->find($valCle); //a generalisé
    if ($options['dim'] === false || ! is_numeric($options['dim']))
    {
      //on renvois telquel
      $dataImage = $ficged[$options['donnees']];
      $sizeImage = strlen($dataImage);
    }
    else
    {
      //on redimenssionne
      $image = $ficged[$options['donnees']];
      $max = $options['dim'];
      $fichierSource = tempnam(sys_get_temp_dir(), 'GED');
      $fichierResult = $fichierSource.'redim';
      file_put_contents($fichierSource, $image);
      
      list($largOrig, $hautOrig) = getimagesize($fichierSource);
      if ($largOrig > $max || $hautOrig > $max)
      {
        $ratio = $largOrig/$hautOrig;
        if ($largOrig > $hautOrig)
        {
          $largeur = $max;
          $hauteur = (int)$max / $ratio;
        }
        else
        {
          $hauteur = $max;
          $largeur = (int)$max * $ratio;
        }
        //resampling
        $imageResult = imagecreatetruecolor($largeur, $hauteur);
        $imageSource = imagecreatefromjpeg($fichierSource);
        imagecopyresampled($imageResult, $imageSource, 0, 0, 0, 0, $largeur, $hauteur, $largOrig, $hautOrig);
        //on construit l'appercut et on fais le menage
        imagejpeg($imageResult, $fichierResult, 80);
        imagedestroy($imageResult);
        imagedestroy($imageSource);
      }
      $dataImage = file_get_contents($fichierResult);
      $sizeImage = filesize($fichierResult);
      //on nettoie les fichiers
      unlink($fichierResult);
      unlink($fichierSource);
    }
    
    
    header("Content-Type: image/jpeg");
    header("Content-Length: ".(string)$sizeImage);
    if ($options['download'])
    {
      header("Content-Disposition: attachment; filename=document.jpg"); 
      header("Content-Type: application/force-download" );    
      header("Content-Type: application/download" );
    }
    echo $dataImage;
  }
}
?>
