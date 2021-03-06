<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmException
@file : mmExceptionHttp.php
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

class mmExceptionHttp extends mmException{
    const NOT_FOUND         = 404;
    const FORBIDDEN         = 403;
    const INTERNAL_ERROR    = 500;
    const BAD_REQUEST       = 400;
    const UNAUTHORIZED      = 401;

    public function __construct($code = null, $previous = null) {
        $message = '';
        switch ($code) {
            case self::NOT_FOUND:
                $message = '<h1>404 - Not Found</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 404 Not Found');
                $this->code = self::NOT_FOUND;
                break;
            case self::FORBIDDEN:
                $message = '<h1>Acces interdit</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 403 Forbidden');
                $this->code = self::FORBIDDEN;
                break;
            case self::INTERNAL_ERROR:
                $message = '<h1>Erreur interne</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 500 Internal Error');
                $this->code = self::INTERNAL_ERROR;
                break;
            case self::BAD_REQUEST:
                $message = '<h1>Request malform&eacute;e</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 400 Bad Request');
                $this->code = self::BAD_REQUEST;
                break;
            case self::UNAUTHORIZED:
                $message = '<h1>Unauthorized</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 400 Bad Request');
                $this->code = self::UNAUTHORIZED;
                break;
            default:
                $message = "<h1>Erreur HTTP $code inconnue.</h1>".new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 400 Bad Request');
                $this->code = self::BAD_REQUEST;
                break;
        }
        parent::__construct($message, $code, $previous);
    }
}
