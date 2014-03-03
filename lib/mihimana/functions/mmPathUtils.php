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
  @file : mmPathUtils.php
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
 * Retourne l'action courante
 * @return type 
 */
function getAction() {
    return ACTION_COURANTE;
}

/**
 * retourne le module courant
 * @return type 
 */
function getModule() {
    return MODULE_COURANT;
}

/**
 * retourne l'application courante
 * @return type 
 */
function getApplication() {
    return APPLICATION;
}

/**
 * Retourne le chemin vers le repertoire de templates de l'application courante
 * @return type 
 */
function getTemplatesPath() {
    if (PROGRAMME_STANDARD) {
        return MIHIMANA_DIR . '/builtinModule/templates';
    } else {
        return APPLICATION_DIR . '/templates';
    }
}

function getTemplate() {
    
}