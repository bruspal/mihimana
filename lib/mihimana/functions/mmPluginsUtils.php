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
  @file : mmPluginsUtils.php
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
 * This methode automatically include plugins, it first check in PLUGINS_DIR dir (application specifique) then in MM_PLUGINS_DIR (globalized plugins pool)
 * @param mixed $plugin string or array of string to the relative path to plugin's bootstrap
 * @throws mmExceptionDev thrown when plugin not found
 */
function loadPlugin($plugin) {
    $plugin = (array) $plugin;
    foreach ($plugin as $p) {
        $path = PLUGINS_DIR.DIRECTORY_SEPARATOR.$p;
        if (file_exists($path)) {
            require_once $path;
        } else {
            $path = MM_PLUGINS_DIR.DIRECTORY_SEPARATOR.$p;
            if (file_exists($path)) {
                require_once $path;
            } else {
                mmStatusInternalError();
                throw new mmExceptionDev("usePlugins() : plugins <i>$p</i> not found");
            }
        }
    }
}

