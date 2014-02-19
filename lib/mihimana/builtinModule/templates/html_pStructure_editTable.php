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
@file : html_pStructure_editTable.php
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


//en attendant de mettre en place le system de generation de la balise form
if ($form->isNew()) {
  $action = "?module=pStructure&action=creerTable";
}
else {
  $action = "?module=pStructure&action=majTable";
}
?>
<h1>Edition de la table</h1>
<form method="post" action="<?php echo $action ?>">
  <?php echo $form->renderFieldset('Nom table', array('nom_table', 'emplacement', 'description')); ?>
</form>