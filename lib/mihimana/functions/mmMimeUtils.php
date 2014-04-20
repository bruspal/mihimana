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
  @file : mmMimeUtils.php
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
 * Output $content as PDF file
 * @param buffer $content file content to send to the client
 * @param boolean $forceDownload if true force the client to download the file
 */
function mmOutputPdf($content, $forceDownload = false) {
    header("Content-type: application/pdf");
    header("Content-Length: " . (string) strlen($content));
    if ($forceDownload !== false) {
        if ($forceDownload === true) {
            $forceDownload = "document";
        }
        header("Content-Disposition: attachment; filename=$forceDownload.pdf");
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
    }

    echo $content;
}

/**
 * Output $content as Jpeg file
 * @param buffer $content file content to send to the client
 * @param boolean $forceDownload if true force the client to download the file
 */
function mmOutputJpeg($content, $forceDownload = false) {
    header("Content-type: image/jpeg");
    header("Content-Length: " . (string) strlen($content));
    if ($forceDownload !== false) {
        if ($forceDownload === true) {
            $forceDownload = "document";
        }
        header("Content-Disposition: attachment; filename=$forceDownload.jpg");
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
    }

    echo $content;
}

/**
 * Set output as HTML
 * @param string $encoding if ommited the APP_DEFAULT_ENCODING defined in APP/config/config.php will be used 
 */
function mmOutputHtml($encoding = APP_DEFAULT_ENCODING) {
    header("Content-Type: text/html; charset=$encoding");
    header("charset: $encoding");
}

/**
 * Set output as JSON
 */
function mmOutputJson() {
    header('Content-Type: application/json');
}

/**
 * Set output as JSON
 */
function mmOutputJsonp() {
    header('Content-Type: application/javascript');
}
