<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : %package%
@module: %module%
@file : %file%
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

$licenseText = "<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : tools
@module: 
@file : fixLicence.php
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

";

// traitement des fichier recursivement
$repertoire = '/home/bruno/projets/toto/lib/mihimana';
$parcourt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($repertoire));
foreach ($parcourt as $fichier) {
    $fichierCourant = $fichier->getBasename();
    $chemin = $fichier->getPathname();
    $fileExt = $fichier->getExtension();
    $package = 'lib';
    $module = substr(dirname(substr($chemin, strlen($repertoire))), 1);
    if ($fileExt == 'php') {
        $fileContent = file_get_contents($chemin);

        $activeLicense = str_replace(array('%package%', '%module%', '%file%'), array($package, $module, $fichierCourant), $licenseText);
        if (strpos($fileContent, $activeLicense) === false) {
            //y'a pas de license on ajoute
            echo "$fichierCourant : ";
            $newContent = preg_replace('/<\?php/', $activeLicense, $fileContent, 1);
            if (file_put_contents($chemin, $newContent) !== false)
                echo "OK\n";
            else
                echo "PAS OK\n";
        }
    }
}
?>
