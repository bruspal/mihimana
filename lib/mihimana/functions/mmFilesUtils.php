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
  @file : mmFilesUtils.php
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
 * Vide le reperoire $directory, si $recursive = true (valeur par defaut) vide aussi les sous repertoire.<br />
 * En cas d'erreur envois une exception mdExceptionRessource
 * @param DirectoryIterator $directory
 * @param type $recursive
 * @throws mmExceptionRessource 
 */
function mmEmptyDirectory($directory, $recursive = true) {
    $directory = new DirectoryIterator($directory);
    foreach ($directory as $file) {
        $filePath = $file->getPathname();
        if (!$file->isDot()) { //On ne traite pas les repertoire '.' et '..'
            if ($file->isDir() && $recursive) {
                //Si c'est un sous repertoire et qu'on effectue une suppression recursive on vide le sous repertoire
                mmEmptyDirectory($filePath, true);
            } else {
                //c'est un fichier ou un lien, on le supprime
                if (!unlink($filePath)) {
                    throw new mmExceptionRessource("Echec de la suppression du fichier $filePath");
                }
            }
        }
    }
}

/**
 * Recursively copy $from to $to
 * @param type $from
 * @param type $to
 * @return boolean
 */
function mmRecursiveCopy($from, $to) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            mkdir($to . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            copy($item, $to . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
    return true;
}

/**
 * change access right recursively
 * @param type $dir : root directory or file
 * @param type $fileMode : mask for file (default 0666)
 * @param type $dirMode : mask for directory (default (0777)
 * @return boolean
 */
function mmRecursiveChmod($dir, $fileMode = 0666, $dirMode = 0777) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            chmod($item, $dirMode);
        } else {
            chmod($item, $fileMode);
        }
    }
    return true;
}