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
@file : html_pUser.php
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
  <?php echo $form->renderButtons() ?>
  <fieldset>
    <legend>Edition d'un utilisateur</legend>
    <table>
      <tr>
        <th>Login</th>
        <td><?php echo $form['login'] ?></td>
      </tr>
      <tr>
        <th>Mot de passe</th>
        <td><?php echo $form['password'] ?></td>
      </tr>
        <tr>
        <th>Actif</th>
        <td><?php echo $form['actif'] ?></td>
      </tr>
      <tr>
        <th>est super admin</th>
        <td><?php echo $form['super_admin'] ?></td>
      </tr>
      <tr>
        <th>Nom</th>
        <td><?php echo $form['nom'] ?></td>
      </tr>
      <tr>
        <th>Prénom</th>
        <td><?php echo $form['prenom'] ?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?php echo $form['email'] ?></td>
      </tr>
    </table>
  </fieldset>
</form>