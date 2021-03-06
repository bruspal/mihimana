<h1>Edition de la table</h1>
<form action="?module=pStructure&action=editChamps" method="post">
  <div class="navigation">
    <?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: builtinModule/templates
@file : html_pStructure_editChamp.php
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

 echo $form->renderButtons() ?>
  </div>
  <table class="list">
    <thead>
      <tr>
        <th style="width: 20em;">Nom du champ</th>
        <th style="width: 20em;">Libellé</th>
        <th style="width: 20em;">Type</th>
        <th style="width: 10em;">Cle</th>
        <th style="width: 20em;">Operation</th>
      </tr>
    </thead>
    <?php foreach($colonnes as $champ): ?>
    <tbody>
      <tr>
        <td><?php echo $champ['nom_champ'] ?></td>
        <td><?php echo $champ['libelle'] ?></td>
        <td><?php echo $champ['type_champ'] ?></td>
        <td><?php echo $champ['est_primary']?'Oui':'Non' ?></td>
        <td>
          <a href="#" onclick="openWindow('<?php echo genereUrlProtege("?module=pStructurePopup&action=editer&b=id=".$champ['id']) ?>')">Modifier</a>
          Supprimer
        </td>
      </tr>
    </tbody>
    <?php endforeach; ?>
  </table>
  
  
</form>