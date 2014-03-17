<?php
/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: functions
  @file : mmHelpersUtils.php
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
  ------------------------------------------------------------------------------ */

function loadHelper($helperName) {
    $helperName = 'mmHelper'.ucfirst($helperName).'.php';
    if (file_exists(HELPERS_DIR.DIRECTORY_SEPARATOR.$helperName)) {
        require_once HELPERS_DIR.DIRECTORY_SEPARATOR.$helperName;
    } else {
        if (file_exists(MM_HELPERS_DIR.DIRECTORY_SEPARATOR.$helperName)) {
            require_once MM_HELPERS_DIR.DIRECTORY_SEPARATOR.$helperName;
        } else {
            throw new mmExceptionDev("echec du chargement du helper $helperName<br>Verifier que ce fichier se trouve dans ".HELPERS_DIR." ou ".MM_HELPERS_DIR);
        }
    }
}