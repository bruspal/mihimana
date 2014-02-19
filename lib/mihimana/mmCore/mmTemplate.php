<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmCore
@file : mmTemplate.php
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



class mmTemplate extends mmObject {

    public static function renderTemplate($templateName, $variables = array(), $path = '') {
        if (!$path) {
            $path = dirname(dirname(__FILE__)) . '/templates';
        }
        //Creation des variables utilise dans le template
        foreach ($variables as $vn => $vv) {
            $$vn = $vv;
        }
        ob_start();
        require "$path/$templateName";
        $result = ob_get_clean();

        return $result;
    }

}

?>