<?php
/*
 * Le tableau credential permet de définir les droits d'acces anonyme par module et par module/action le format est:
 *  * pour les module seul : 'nom_du_module' => false | true
 *  * pour les module est les actions : 'nom_du_module/nom_de_l_action' => false | true
 * 
 * Avec false pour les authorisé l'acces non identifié et true ou l'authentification est obligatoire
 * 
 * /!\ : les authorisations fonctionnent selon le principe suivant : on regarde module/action si absent on regarde module. Si ni l'un ni l'autre ne sont défini l'acces est automatiquement equivalent a true
 */
$credentials = array(
    
);