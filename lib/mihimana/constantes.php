<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: 
@file : constantes.php
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


//Fichier qui stock et defini les constantes
/*
 * Constantes de chemins
 */
define('BASE_DIR', dirname(dirname(dirname(__FILE__))));
define('WEB_DIR', BASE_DIR.'/web');
define('APPLICATION_DIR', BASE_DIR.'/'.APPLICATION);
define('LIB_DIR', BASE_DIR.'/lib');
define('MODELS_DIR', APPLICATION_DIR.'/lib/models');
define('MIGRATION_DIR', APPLICATION_DIR.'/lib/temp');
define('FIXTURE_DIR', LIB_DIR.'/fixtures');
define('MIHIMANA_DIR', LIB_DIR.'/mihimana');
define('CONFIG_DIR', LIB_DIR.'/config');
define('SQLITE_DIR', LIB_DIR.'/sqlite');
define('PLUGINS_DIR', LIB_DIR.'/plugins');
define('DOCTRINE_DIR', PLUGINS_DIR.'/Doctrine-1.2.4');

/*
 * Constantes pdf
 */
define('MM_PDF_AUTHOR', 'MihiMana Engine');
define('MM_PDF_CREATOR', 'MihiMana Engine');
/*
 * Precision mathématique et devise
 */
define('MM_DEVISE_DECIMAL', 0);
define('MM_DEVISE_SYMBOL', '');
?>
