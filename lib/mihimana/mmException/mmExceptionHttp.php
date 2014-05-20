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

    public function __construct($code = null, $previous = null) {
        $message = '';
        switch ($code) {
            case self::NOT_FOUND:
                $message = '<h1>404 - Not Found</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 404 Not Found');
                break;
            case self::FORBIDDEN:
                $message = '<h1>Acces interdit</h1>'.new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 403 Forbidden');
                break;
            default:
                $message = "<h1>Erreur HTTP $code inconnue.</h1>".new mmWidgetButtonGoPage('Accueil', url('@home'))."<fieldset>$message</fieldset>";
                header('HTTP/1.0 404 Not Found');
                break;
        }
        parent::__construct($message, $code, $previous);
    }
}
