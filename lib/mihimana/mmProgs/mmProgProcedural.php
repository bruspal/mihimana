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
@file : mmProgProcedural.php
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



class mmProgProcedural extends mmProg {
    //

    /**
     * Execute l'action $action et affiche le resultat
     * @param string $action
     * @param array $request
     * @return boolean 
     */
    public function execute($action, mmRequest $request) {
        //On sauvegarde les parametres passé au programme
        $this->parametresProgramme = $request;
        //demarage du buffer
        ob_clean();
        ob_start();
        //execution
        $codeSortie = $this->principale($action, $request);
        //recuperation du buffer de sortie
        $sortieProgramme = ob_get_clean();
        //Application du layout de l'ecran si un layout existe
        if ($this->template && file_exists(getTemplatesPath() . '/' . $this->template)) {
            //y'a un template, on le parse
            $sortieLayout = mmTemplate::renderTemplate($this->template, $this->variables, getTemplatesPath());
        } else {
            //pas de templates associe, on a une chaine vide
            $sortieLayout = '';
        }
        //on chaine le layout au sortie brute du programme
        $sortieProgramme = $sortieProgramme . $sortieLayout;

        if (!AJAX_REQUEST && $this->layout) {
            $sortieFinale = mmTemplate::renderTemplate($this->layout, array('sortieProgramme' => $sortieProgramme), APPLICATION_DIR . '/templates');
        } else {
            $sortieFinale = $sortieProgramme;
        }
        //affichage de l'ecran
        $this->genereHtmlFinal($sortieFinale);

        return true;
    }

    /**
     * Principale est le point d'entrée principale d'un programme utilisateur
     * @param type $action
     * @param type $parametres 
     */
    public function principale($action = '', $parametres = null) {
        
    }

}

?>
