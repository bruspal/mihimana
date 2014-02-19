<?php
/*------------------------------------------------------------------------------
-------------------------------------
Mihimana : the visual PHP framework.
Copyright (C) 2012-2014  Bruno Maffre
contact@bmp-studio.com
-------------------------------------

-------------------------------------
@package : lib
@module: mmProgs
@file : mmProgWebService.php
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



class mmProgWebService extends mmProg {

    public function configure(mmRequest $request) {
        $this->setLayout(null);
        $this->setTemplate(null);
    }

    public function execute($action = '', mmRequest $request = null) {
        if (AJAX_REQUEST || DEBUG) {
            try {
                //demarage du buffer
                ob_clean();
                ob_start();
                //execution
                $codeSortie = $this->principale($action, $request);
                //recuperation du buffer de sortie
                $sortieProgramme = ob_get_clean();
                $this->genereHtmlFinal($sortieProgramme);
            } catch (Exception $e) {
                if (DEBUG) {
                    echo mdAjaxError($e->getMessage() . '<br /><pre>' . $e->getTraceAsString() . '</pre>');
                } else {
                    echo mdAjaxError("Une erreur interne s'est produite");
                }
            }
        } else {
            //Ici on ne fais rien, si on tente d'executer un service web en mode standard on renvois un ecran vide.
            header('HTTP/1.1 403 Forbidden'); //on ecrit acces refusé standard
        }
    }

    public function principale($action = '', $parametres = null) {
        
    }

}

?>
