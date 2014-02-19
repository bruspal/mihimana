<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmCore
@file : mmRequest.php
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



class mmRequest extends mmObject implements ArrayAccess
{
  protected $parametres;
  
  public function __construct($parametres = false) {
    if ( ! $parametres)
    {
      $parametres = $_REQUEST;
    }
    
    //Si on a un parametre de type url code par table de hashage on recupere l'url code
    if (isset($parametres['ucah']))
    {
      $cleDeHashage = $parametres['ucah'];
      $listeUrls = mmSession::get('__tableauUrls__', false); //On recupere la liste des url enregistré dans la session on stock ca dans le tableau $listeUrl
      if ($listeUrls) // On a bien recupere le tableau des url
      {
        if (isset($listeUrls[$cleDeHashage]))
        {
          //La cle de hashage existe dans le tableau des cle de hashage, on va recuperer les données stocker
          $urlStockee = $listeUrls[$cleDeHashage];
          //on recupere les parametres et on les mets dans le tableau
          $urlParsee = parse_url($urlStockee);
          $valeurs = $urlParsee['query'];
          parse_str($valeurs, $tableauTemp);
          //on vire la cle de hashage
          unset ($parametres['ucah']);
          //On termine l'initialisation en fournissant l'ensemble des parametre hors ucah
          $this->parametres = array_merge($parametres, $tableauTemp);
          //On arrive la? On nettoie la session.
          mmSession::remove('__tableauUrls__');
        }
        else
        {
          //la cle de hashage n'existe pas dans le tableau generé, c'est une erreur
          throw new mmExceptionControl("L'url demandé est introuvable");
        }
      }
    }
    else
    {
      //On a pas d'url protegé
      //TODO: voir si on autorise les url non protegé a terme. Pour le moment non sauf si on est en mode debug
      if (true || DEBUG)
      {
        $this->parametres = $parametres;
      }
      else
      {
        //si on a pas fournis de parametres, on considere que c'est la page par defaut. Dans ce cas la c'est pas une erreur, simplement la table des parametres est vide
        if (count($parametres) == 0)
        {
          $this->parametres = array();
        }
        else
        {
          //sinon c'est une erreur bloquante
          throw new mmExceptionControl("format d'url non authorisé");
        }
      }
    }
  }
  
  public function getParam($nomVar, $default = null) {
    if (isset($this->parametres[$nomVar])) {
      return $this->parametres[$nomVar];
    }
    else {
      return $default;
    }
  }
  
  public function get($nomVar, $default = null) {
    return $this->getParam($nomVar, $default);
  }
  
  public function setParam($nomVar, $valeur) {
    $this->parametres[$nomVar] = $valeur;
  }
  
  public function set ($nomVar, $valeur) {
    $this->parametres[$nomVar] = $valeur;
  }
  
  /**
   * Retourne l'ensemble des parametres dans un tableau associatif
   * @return type 
   */
  public function getTableauParam()
  {
    return $this->parametres;
    mmUser::flashDebug('getTableauParam: utiliser toArray a la place');
  }

  /**
   * Retourne l'ensemble des parametres dans un tableau associatif
   * @return type 
   */
  public function toArray()
  {
    return $this->parametres;
  }
  
  /*
   * Method pour l'acces de type array
   * ceci permet de gerer les parametres comme un tableau
   */
  public function offsetGet($offset)
  {
    if (isset($this->parametres[$offset]))
    {
      return $this->parametres[$offset];
    }
    else
    {
      throw new mmExceptionDev("le parametre $offset n'existe pas");
    }
  }
  
  public function offsetSet($offset, $value)
  {
    $this->parametres[$offset] = $value;
  }
  
  public function offsetUnset($offset)
  {
    unset($this->parametres[$offset]);
  }
  
  public function offsetExists($offset)
  {
    return isset($this->parametres[$offset]);
  }
}
?>
