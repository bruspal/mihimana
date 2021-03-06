<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: templates
@file : widgetAdmin.php
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


//nommage
$cl = 'cl_'.$this->getName();
$cr = 'cr_'.$this->getName();
$el = 'el_'.$this->getName();
$er = 'er_'.$this->getName();
$vl = 'vl_'.$this->getName();
$vr = 'vr_'.$this->getName();

//configuration
$creerleft = new mmWidgetList($cl, array());
$creerleft->setAdminMenu(false);
$creerleft->setId('cl_'.$this->getId());

$creerright = new mmWidgetList($cr);
$creerright->setAdminMenu(false);
$creerright->setId('cr_'.$this->getId());

$editerleft = new mmWidgetList($el, array());
$editerleft->setAdminMenu(false);
$editerleft->setId('el_'.$this->getId());

$editerright = new mmWidgetList($er);
$editerright->setAdminMenu(false);
$editerright->setId('er_'.$this->getId());

$voirleft = new mmWidgetList($vl, array());
$voirleft->setAdminMenu(false);
$voirleft->setId('vl_'.$this->getId());

$voirright = new mmWidgetList($vr);
$voirright->setAdminMenu(false);
$voirright->setId('vr_'.$this->getId());

$aideEnLigne = new mmWidgetTextArea('a_'.$this->getName(), '', array('cols'=>40, 'rows'=>3));
$aideEnLigne->setAdminMenu(false);
$aideEnLigne->setId('a_'.$this->getId());

?>

<div class="admin_menu" id="admin_<?php echo $this->getId() ?>" style="border: 2px groove #FF0000; padding: 5px; width: 400px; background-color: #FFFF00; position: absolute; display: none; opacity: 0.8;">
  <div id="err_<?php echo $this->getId() ?>" style="font-weight: bolder; color: red;"></div>
  <table border="1" style="text-align: center;">
    <tr><td colspan="3">creer</td></tr>
    <tr>
      <th width="45%">Groupe disponible</th>
      <th width="10%">&nbsp;</th>
      <th width="45%">Groupe autorise</th>
    </tr>  
    <tr>
      <td><?php echo $creerleft->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $creerleft->getId(), $creerright->getId()))) ?></td>
      <td>
        <button type="button" onclick="listToList('<?php echo $creerleft->getId() ?>', '<?php echo $creerright->getId() ?>')">&rarr;</button><br />
        <button type="button" onclick="listToList('<?php echo $creerright->getId() ?>', '<?php echo $creerleft->getId() ?>')">&larr;</button>
      </td>
      <td><?php echo $creerright->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $creerright->getId(), $creerleft->getId()))) ?></td>
    </tr>
    <tr><td colspan="3">editer</td></tr>
    <tr>
      <th>Groupe disponible</th>
      <th>&nbsp;</th>
      <th>Groupe autorise</th>
    </tr>  
    <tr>
      <td><?php echo $editerleft->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $editerleft->getId(), $editerright->getId()))) ?></td>
      <td>
        <button type="button" onclick="listToList('<?php echo $editerleft->getId() ?>', '<?php echo $editerright->getId() ?>')">&rarr;</button><br />
        <button type="button" onclick="listToList('<?php echo $editerright->getId() ?>', '<?php echo $editerleft->getId() ?>')">&larr;</button>
      </td>
      <td><?php echo $editerright->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $editerright->getId(), $editerleft->getId()))) ?></td>
    </tr>
    <tr><td colspan="3">voir</td></tr>
    <tr>
      <th>Groupe disponible</th>
      <th>&nbsp;</th>
      <th>Groupe autorise</th>
    </tr>  
    <tr>
      <td><?php echo $voirleft->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $voirleft->getId(), $voirright->getId()))) ?></td>
      <td>
        <button type="button" onclick="listToList('<?php echo $voirleft->getId() ?>', '<?php echo $voirright->getId() ?>')">&rarr;</button><br />
        <button type="button" onclick="listToList('<?php echo $voirright->getId() ?>', '<?php echo $voirleft->getId() ?>')">&larr;</button>
      </td>
      <td><?php echo $voirright->render(array("ondblclick"=>sprintf ("listToList('%s', '%s')", $voirright->getId(), $voirleft->getId()))) ?></td>
    </tr>
    <tr>
      <td colspan="3">Aide en ligne</td>
    </tr>
    <tr>
      <td colspan="3"><?php echo $aideEnLigne ?></td>
    </tr>
  </table>
  <button type="button" name="valide" onclick="submitDroits('#<?php echo $this->getId() ?>', '<?php echo $this->getName() ?>');">Valider</button>
  <button type="button" name="annuler" onclick="$('#admin_<?php echo $this->getId() ?>').hide(200);">Annuler</button>
</div>
<script type="text/javascript">
  $('#<?php echo $this->getId() ?>').bind("contextmenu", function(e) {
    showAdminMenu(this, '<?php echo $this->getName() ?>');
    return false;
  });
</script>