<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: functions
@file : mmDebugUtils.php
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

/**
 * Flash debug message related too deprecated methodes
 * @param string $class class name
 * @param string $oldMethode old methode name
 * @param string $newMethode new methode name
 */
function deprecatedMethode($class, $oldMethode, $newMethode) {
    mmUser::flashDebug("Attention $class::$oldMethode obsolete ! utiliser $class::$newMethode a la place !<br />module : ".MODULE_COURANT.'/'.ACTION_COURANTE);
}