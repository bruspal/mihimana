<h1>Edition des tables</h1>
<fieldset>
  <legend>Tables existantes</legend>
  <table class="list">
    <thead>
      <tr>
        <th>Nom</th>
        <th>Operation</th>
      </tr>
    </thead>
    <tbody>
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
@file : html_pStructure.php
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

 foreach($listeTables as $table): ?>
      <tr>
        <td><?php echo $table ?></td>
        <td>
          <a href="<?php echo genereUrlProtege("?module=pStructure&action=editTable&table=$table") ?>">Modifier</a> |
          <a href="<?php echo genereUrlProtege("?module=pStructure&action=editChamps&table=$table") ?>">Editer structure</a> |
          <a href="#" onclick="if(confirm('Souhaitez vous supprimer cette table et toutes les champs associé ?')) goPage('<?php echo genereUrlProtege("?module=pStructure&action=supprimeTable&table=$table") ?>')">Supprimer</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="navigation">
    <?php echo $form->renderButtons() ?>
  </div>
</fieldset>

