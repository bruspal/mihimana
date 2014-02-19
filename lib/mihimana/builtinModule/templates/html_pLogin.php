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
@file : html_pLogin.php
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

 echo $form->renderFormHeader() ?>
  <table class="login_table">
    <tr>
      <td colspan="3" class="titre">
        Veuillez vous identifier
      </td>
    </tr>
    <tr>
      <th>Utilisateur</th>
      <th>Mot de passe</th>
      <td></td>
    </tr>
    <tr>
      <td><?php echo $form['login'] ?></td>
      <td><?php echo $form['password'] ?></td>
      <td><input type="submit" value="Valider" /></td>
    </tr>
  </table>
</form>
