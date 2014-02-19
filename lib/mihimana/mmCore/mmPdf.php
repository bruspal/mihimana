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
@file : mmPdf.php
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



require_once PLUGINS_DIR.'/html2pdf/html2pdf.class.php';

class mmPdf extends HTML2PDF {
  
  protected
          $name,
          $erreur,
          $debug;
          
  public function __construct($parametres = array(), $orientation = 'P', $format = 'A4', $langue = 'fr', $unicode = true, $encoding = 'UTF-8')
  {
    parent::__construct($orientation, $format, $langue, $unicode, $encoding);
    $this->debug = false;
    //$this->configure($parametres);
  }
  
  public function configure($parametres = array())
  {
    //on analyse les parametres
    $options = new mmOptions($parametres, array(
        'gauche'=>15,
        'droite'=>15,
        'haut'=>15,
        'bas'=>15,
        'font'=>'Arial',
        'author'=>MM_PDF_AUTHOR,
        'creator'=>MM_PDF_CREATOR
    ));
    $this->pdf->SetLeftMargin($options->get('gauche'));
    $this->pdf->SetRightMargin($options->get('droite'));
    $this->pdf->SetTopMargin($options->get('haut'));
    $this->pdf->SetAutoPageBreak(true, $options->get('bas'));
    $this->setDefaultFont($options->get('font'));
    $this->pdf->SetAuthor($options->get('author'));
    $this->pdf->SetCreator($options->get('creator'));
    
  }
  
  public function generateFromScreen(mmScreen $screen, $mode = 'S')
  {
    $corp = $screen->render(false);
    $header = new mmScreen('_enteteP1_');
    $hHeader = $header->render(false);
    $footer = new mmScreen('_footerP1_');
    $hFooter = $footer->render(false);
    
    $pages = array();
//    $nbLigneTotal = decoupeHtml($corp, $ligneParPage, $colonnePage, $pages);
    
    $content = '
    <style>
    * {
    font-size: 10px;
    }
    h1 {
    font-size: 30px;
    }
    h2 {
    font-size: 25px;
    }
    h3 {
    font-size: 20px;
    }
    h4 {
    font-size: 15px;
    }
    h5 {
    font-size: 10px;
    }
    h5 {
    font-size: 5px;
    }
    
    </style>
    ';
    $content .= '<page backtop="30" backbottom="15">';
    $content .= "
      <page_header>
      $hHeader
      </page_header>
      <page_footer>
      $hFooter
      </page_footer>
    ";
    $content .= $corp;
    $content .= "</page>";
    return $this->generate($content);
  }
  
  public function generate($content, $mode = 'S')
  {
    try
    {
      $this->writeHTML($content, $this->debug);
      $strPdf = $this->OutPut($this->name, $mode);
    }
    catch(HTML2PDF_exception $e)
    {
      if (DEBUG)
      {
        $this->erreur = $e->getMessage();
      }
      else
      {
        $this->erreur = "Une erreur s'est produite dans la génération du document";
      }
      return false;
    }
    
    return $strPdf;
  }
  
  public function getError()
  {
    return $this->erreur;
  }
  
  public function debugOn()
  {
    $this->debug = true;
  }
  
  public function debugOff()
  {
    $this->debug = true;
  }
  
}


function CompteLigneDecoupeHtml($source, & $pages = null, $nbLignePage = 70, $nbColonnePage = 130)
{
  
  $nbrLigne = 0;
  $pageCourante = '';
  $index = 0;
  $tranche = $source;
  
  $regexp = "#^("; //le source commence par
  $regexp.= "<[^<]+>|"; // une balise ouvrante OU
  $regexp.= "</[^<]+>|"; // une balise fermante OU 
  $regexp.= "<[^<]+/\s*>|"; //une balise autofermante OU
  $regexp.= "[^<]+"; // n'importe quoi d'autre sauf un '<'
  $regexp.= ")#i";
  
  while ($tranche != '')
  {
    $elementTrouve = preg_match($regexp, $tranche, $fragPrincipale); //on recherche le bloc 
  }
}
?>
