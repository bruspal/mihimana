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
@file : pGed.php
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


/**
 * pGed: 
 * $parametres o=...
 * <ul>
 * <li><b>context=nom du context</b>: le context utilisé pour l'echange de données entre l'application et la ged. par defaut __contextDataGed__</li>
 * <li><b>cleged=nom du tableau cle</b>: Lors de l'utilisation du transfert d'informations via context defini le nom du tableau contenant la cle ged</li>
 * <li><b>cle=valeur 0,...,valeur 9</b>: valeur de cle fournis sous forme de chaine chaque valeur est separé par ','</li>
 * <li><b>stockage=type de stockage</b>: 'sql': stockage dans la base sql (defaut) ou 'file' stockage en fichier 
 * <li><b>geddir=...</b>: Chemin racine de stockage des documents dans le cas du stockage 'file'. Par defaut APPLICATION_DIR.'/ged'
 * </ul>
 *   
 */
class pGed extends mmProgProcedural
{

  protected 
          $contextScan,
          $contextData,
          $cleGed,
          $options;

  public function main(mmRequest $request)
  {
    $this->setLayout('popup');
    $optionsDefaut = array(
        'context'=>'',  //context de reference
        'cleged'=>'cleGed',
        'cle'=>false,
        'stockage'=>'sql',
        'geddir'=>APPLICATION_DIR.'/ged',
        'miniature'=>100,
        'previsu'=>500,
        'qualite'=>60,
        't'=>0,
        //peut etre a virer
        'nomFichier'=>'toto.jpg',
    );
    if (HTTPS)
    {
      $this->baseUrl = 'https://'.$_SERVER['HTTP_HOST'];//.$SERVER['REQUEST_URI'];
    }
    else
    {
      $this->baseUrl = 'http://'.$_SERVER['HTTP_HOST'];//.$SERVER['REQUEST_URI'];
    }
    
    $options = mmParseOptions($request->getParam('o',0), $optionsDefaut);
    
    $this->nomFichier = $options['nomFichier'];
    
    //initialisation des contexts
    //de travail
    if ($options['context'] != '')
    {
      // soit on prend le context fournis en parametre
      $this->contextData = new mmContext($options['context']);
    }
    else
    {
      // Sinon on prend le context standard de la GED
      $this->contextData = new mmContext('__contextDataGed__');
    }
    $this->contextScan = new mmContext('__contextScan__');

    //cle principale
    //On construit une eventuel serie de cle fournis en parametre
    $cleParam = $options['cle'];
    if ($cleParam !== false)
    {
      $cles=explode(',', $cleParam);
      $cleParam = array();
      for ($i = 0; $i < 10; $i++)
      {
        if (isset($cles[$i]))
        {
          $cleParam['cle_'.$i] = $cles[$i];
        }
      }
    }
    if ($cleParam != false)
    {
      $this->cleGed = $cleParam;
      $this->contextScan->set('cleGed', $this->cleGed);
      if ($this->contextData->exists('cleged'))
      {
        $this->contextData->set('cleged', $this->cleGed);
      }
    }
    else
    {
      $this->cleGed = $this->contextData->get($options['cleged'], $this->contextScan->get('cleGed', false));
    }
    if (!$this->cleGed || count($this->cleGed) <= 0)
    {
      echo mmErrorMessage("Cle GED manquante ou mal initialisé");
      $this->setTemplate(false);
      return false;
    }
    switch (ACTION_COURANTE) {
    case 'scan':
      try
      {
        $this->faisScan();
        $this->contextScan->set('cleGed', $this->cleGed);
      }
      catch (Exception $e)
      {
        $erreur = $e->getMessage()."\n".$e->getTraceAsString()."\nCONTENU DE FILES\n".print_r($_FILES, true);
        file_put_contents('logScan.txt', $erreur);
      }
      break;
    case 'file':
      $this->faisScanManuel();
      $this->contextScan->set('cleGed', $this->cleGed);
      break;
    case 'doc':
      $this->afficheDoc($request);
      break;
    case 'som':
      $this->afficheResume($options['t']);
      break;
    case 'maj':
      $this->majDocument($request);
      break;
    default:
      $this->contextScan->set('cleGed', $this->cleGed);
      $this->genereHtmlScanner();
      break;  
    } 
  }
  
  public function majDocument($parametres)
  {
    $cleUnique = $cleUnique = $this->contextScan->get('cleUnique', false);
    //On recupere l'enregistrement
    $ged = Doctrine_Core::getTable('Ged')->find($cleUnique);
    if ( ! $ged)
    {
      echo mmErrorMessage('Le document n\'a pas été trouvé');
      return false;
    }
    //On met a jour les infos
    $saisie = $parametres['ged'];
    $form = new mmForm($ged);
    if ($form->setValues($saisie))
    {
      //L'affectation a bien été effectué. On sauve
      $form->save();
      $this->redirect('?module=pGed&action=doc');
    }
    else
    {
      echo mmErrorMessage('Une erreur est survenue');
      return false;
    }
    return true;
  }
  
  public function faisScan()
  {
    //preparation du nouveau enregistrement
    $ged = new Ged();
    //preparation de la cle
    
    foreach ($this->cleGed as $colonne=>$valeur)
    {
      $ged[$colonne]=$valeur;
    }
    //on recupère le contenu du fichier
    $nomFichier = $_FILES['file']['tmp_name'][0];
    $this->transformeImage($nomFichier, 100);
    $contenuFichier = file_get_contents($nomFichier);
    $ged['document'] = $contenuFichier;
    $contenuFichier = file_get_contents($nomFichier.'.app');
    $ged['miniature'] = $contenuFichier;
    $ged['type_document'] = 'jpg';
    $ged['date_creation'] = date('Y-m-d H:i:s');
    $ged['titre'] = 'Scan du '.date("d/m/Y à H:i");
    
    $ged->save();
    //on sauvegarde l'id unique (fged_1) dans le context
    $this->contextScan->set('cleUnique', $ged['id']);
  }
  
  public function faisScanManuel()
  {
    //format utilisé
    $format = array('JPG', 'JPEG', 'PDF');
    //preparation du nouveau enregistrement
    $ged = new Ged();
    //preparation de la cle
    foreach ($this->cleGed as $colonne=>$valeur)
    {
      $ged[$colonne]=$valeur;
    }
    //on recupère le contenu du fichier
    $nomFichier = $_FILES['file']['tmp_name'][0];
    $name = $_FILES['file']['name'][0];
    $extension = strtoupper(substr(strrchr($name, '.'),1));
    if (in_array($extension, $format))
    {
      switch ($extension)
      {
        case 'JPG':
        case 'JPEG':
          $this->transformeImage($nomFichier, 100);
          $contenuFichier = file_get_contents($nomFichier);
          $ged['document'] = $contenuFichier;
          $contenuFichier = file_get_contents($nomFichier.'.app');
          $ged['miniature'] = $contenuFichier;
          $ged['type_document'] = 'jpg';
          break;
        case 'PDF':
          $contenuFichier = file_get_contents($nomFichier);
          $ged['type_document'] = 'pdf';
          $ged['document'] = $contenuFichier;
          break;
      }
      $ged['date_creation'] = date('Y-m-d H:i:s');
      $ged['titre'] = 'Document du '.date("d/m/Y à H:i");
      $ged->save();
      //on sauvegarde l'id unique (fged_1) dans le context
      $this->contextScan->set('cleUnique', $ged['id']);
      $this->redirect('?module=pGed&action=doc');
    }
    else
    {
      mmUser::flashError('seul les jpeg et les pdf sont authorisé');
      $this->redirect('?module=pGed');
    }
  }
  
  /**
   * genere l'appercu et recompresse l'image original
   * @param type $fichier
   * @param type $max 
   */
  protected function transformeImage($fichier, $max, $agrandis = false)
  {
    list($largOrig, $hautOrig) = getimagesize($fichier);
    if ($largOrig > $max || $hautOrig > $max || $agrandis)
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
      $imageSource = imagecreatefromjpeg($fichier);
      imagecopyresampled($imageResult, $imageSource, 0, 0, 0, 0, $largeur, $hauteur, $largOrig, $hautOrig);
      //on genere la sortie
      imagejpeg($imageSource, $fichier, 60);
      imagejpeg($imageResult, $fichier.'.app', 60);
      imagedestroy($imageResult);
      imagedestroy($imageSource);
    }
    else
    {
      $imageSource = imagecreatefromjpeg($fichier);      
      imagejpeg($imageSource, $fichier, 60);
      imagejpeg($imageSource, $fichier.'.app', 60);
      imagedestroy($imageSource);
    }
  }
  
  public function afficheResume($tout = 0)
  {
    $where = '';
    for ($i = 0; $i < 10; $i++)
    {
      if (! isset($this->cleGed["cle_$i"]))
      {
        if ($tout == 0)
        {
          $where .= " AND cle_$i = ''";
        }
      }
      else
      {
        $valeur = $this->cleGed["cle_$i"];
        if ($tout == 0)
        {
          $where .= " AND cle_$i = '$valeur'";
        }
        else
        {
          if ($valeur != ''){
            $where .= " AND cle_$i = '$valeur'";
          }
        }
      }
    }
    $where = substr($where, 4);
    $collection = Doctrine_Core::getTable('Ged')->createQuery()->
            select('id')->
            where($where)->
            orderBy('date_creation')->
            execute();
    //on genere le html
    echo '<table style="text-align: center;"><tr>';
    $i = 0;
    foreach ($collection as $document)
    {
      echo '<td style="width: 130px; text-align: center;">';
      echo $document['titre']."<br />";
      switch ($document['type_document'])
      {
        case 'jpg';
          printf('<img src="?module=pJpegSQL&id=%s&o=donnees=miniature" alt="document" onclick="openWindow(\'?module=pGed&action=doc&id=%s\')"/>', $document['id'], $document['id']);
          printf('<br /><a href="?module=pJpegSQL&id=%s&o=download=1" style="background-color: lightgrey; padding: 3px;">TELECHARGER</a>', $document['id']);
          break;
        case 'pdf':
          printf('<div style="height: 100px; width: 150px; background-color: lightgrey; text-align: center; padding-top: 75px;margin-left: auto; margin-right: auto;" onclick="openWindow(\'?module=pGed&action=doc&id=%s\')">DOCUMENT PDF</div>', $document['id']);
          printf('<br /><a href="?module=pPdfSQL&id=%s&o=download=1" style="background-color: lightgrey; padding: 3px;">TELECHARGER</a>', $document['id']);
          break;
        default:
          echo "TYPE INCONNU";
          break;
      }
      
      echo '</td>';
      $i++;
      if ($i == 2)
      {
        echo '</tr><tr>';
        $i = 0;
      }
    }
    echo "</tr></table>";
  }
  
  public function afficheDoc($parametres)
  {
    //on regarde si on a une cle unique dans le contexte
    $cleUnique = $parametres->getParam('id', false);
    if ( ! $cleUnique)
    {
      $cleUnique = $this->contextScan->get('cleUnique', false);
      if ( ! $cleUnique)
      {
        echo mmErrorMessage('Aucun identifiant de document connus');
        return false;
      }
//      //Ici on supprime la cleUnique du context, on a droit a un seul appercu suite au scan. Dans tous les autres cas il faut passer par le tableau de choix
//      $this->contextScan->remove('cleUnique');
    }
    else
    {
      $this->contextScan->set('cleUnique', $cleUnique);
    }
    //on traite le document
    $ged = Doctrine_Core::getTable('Ged')->find($cleUnique);
    if ( ! $ged)
    {
      echo mmErrorMessage('Aucun documents trouvé');
      return false;
    }
    //On charge l'ecran
    $ecran = new mmForm($ged);
    $ecran->setAction('?module=pGed&action=maj');
    $ecran->addWidget(new mmWidgetTextArea($ecran['commentaire'], '', array('cols'=>40, 'rows'=>15)));
    $ecran['titre']->addAttribute('size', 30);
    $ecran->addWidget(new mmWidgetButtonSubmit('Mettre a jour'));
    $ecran->addWidget(new mmWidgetButtonGoPage('Ajouter', '?module=pGed'));
    $ecran->addWidget(new mmWidgetButtonClose());
    //generation du html
    echo $ecran->renderFormHeader();
    ?>
<fieldset>
  <legend>GED</legend>
  <table>
    <tr>
      <td>
        Titre : <?php echo $ecran['titre']; ?><br />
        <?php
          switch($ged['type_document'])
          {
            case 'jpg':
              echo '<img src="?module=pJpegSQL&id='.$ged['id'].'&o=dim=300" alt="document" onclick="openWindow(\'?module=pJpegSQL&id='.$ged['id'].'\', \'gedFull\')" />';
              break;
            case 'pdf':
              echo '<div style="height: 100px; width: 150px; background-color: lightgrey; text-align: center; padding-top: 75px;" onclick="openWindow(\'?module=pPdfSQL&id='.$ged['id'].'\', \'gedFull\')">DOCUMENT PDF</div>';
              break;
          }
        ?>
      </td>
      <td>
        comentaire:<br />
        <?php echo $ecran['commentaire'] ?>
      </td>
    </tr>
  </table>
  <div class="navigation">
    <?php echo $ecran->renderButtons() ?>
  </div>
</fieldset>
    </form>  
    <?php
  }
  
  protected function genereHtmlScanner()
  {
    //on construit le formulaire d'import manuel
    $form = new mmForm();
    $form->setAction('?module=pGed&action=file');
    $form->addWidget(new mmWidgetFile('file[]', array()));
    $form->addWidget(new mmWidgetButtonSubmit('Ajouter'));
    //on genere le HTML
?>
    <fieldset>
      <legend>Ajouter un document</legend>
      <table>
        <tr>
          <td>
            <h2>Ajouter manuellement</h2>
            <?php echo $form->renderFormHeader() ?>
            <?php echo $form['file[]'] ?>
            <?php echo $form['ajouter'] ?>
            </form>
          </td>
        </tr>
        <tr>
          <td>
            <div id="scanApplet">
              <h2>Scanner</h2>
              <applet code="com.asprise.util.jtwain.web.UploadApplet.class" 
                      _codebase="http://asprise.com/product/jtwain/files/"
                      codebase="/jtwain/"
                      archive="JTwain.jar"
                      width="600" height="470">
                      <param name="DOWNLOAD_URL" value="<?php echo $this->baseUrl ?>/jtwain/AspriseJTwain.dll">
                      <param name="DLL_NAME" value="AspriseJTwain.dll">
                      <param name="UPLOAD_URL" value="<?php echo $this->baseUrl ?>/?module=pGed&action=file">
                      <param name="UPLOAD_PARAM_NAME" value="file[]">
                      <param name="FILE_NAME" value="<?php echo $this->nomFichier ?>">
                      <param name="UPLOAD_EXTRA_PARAMS" value="">
                      <param name="UPLOAD_OPEN_URL" value="<?php echo $this->baseUrl ?>/?module=pGed&action=doc">
                      <param name="UPLOAD_OPEN_TARGET" value="_blank">
                      Veuillez installer un navigateur prenant java en charge
              </applet>
            </div>
          </td>
        </tr>
      </table>
    </fieldset>
<?php
  }
}

?>
