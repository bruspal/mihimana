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
/**
 * Load the helper named $helperName. First looks in the HELPERS_DIR (sits in the lib/helpers directory of the application) then in the standard MM_HELPERS_DIR for standard helpers. Throw an exception if helper not found.<br>
 * @param string $_helperName the helper name without extension
 * @throws mmExceptionDev
 */
function loadHelper($helperName) {
    $_helperName = $helperName.'.php';
    if (file_exists(HELPERS_DIR.DIRECTORY_SEPARATOR.$_helperName)) {
        require_once HELPERS_DIR.DIRECTORY_SEPARATOR.$_helperName;
    } else {
        $_helperName = 'mmHelper'.ucfirst($_helperName);
        if (file_exists(MM_HELPERS_DIR.DIRECTORY_SEPARATOR.$_helperName)) {
            require_once MM_HELPERS_DIR.DIRECTORY_SEPARATOR.$_helperName;
        } else {
            throw new mmExceptionDev("echec du chargement du helper $helperName<br>Verifier que ".HELPERS_DIR."/$helperName.php ou ".MM_HELPERS_DIR."/$_helperName existe");
        }
    }
}