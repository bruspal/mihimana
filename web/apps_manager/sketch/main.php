<?php
/*
 * This is the default script to start to manage the application.
 * Change it to any kind of personnal PHP script or generate standardised sketches
 */
class main extends mmProg {
    public function executeIndex(mmRequest $request) {
        echo "<h1>Welcome to your brand new application</h1>";
        if (MODE_INSTALL) {
            echo "<h1>First step installation</h1>";
            echo new mmWidgetButton('creerDB', 'Creer la base de données de parametres', array('onclick' => "if (confirm('CECI VA DETRUIRE LA BASE ETES VOUS SUR DE VOULOIR CONTINUER ?')) goPage('?module=pGestionBase&action=createDBParam')")) . '<br />';
            echo new mmWidgetButtonGoPage('Dumper les data de la base de parametres', "?module=pGestionBase&action=dumpData") . '<br />';
            echo new mmWidgetButtonGoPage('Charger les data de la base de parametres', "?module=pGestionBase&action=loadData") . '<br />';
        } else {
            echo "<p>set mode_install to true in ".WEB_DIR.'/'.APPLICATION.'.php to enable low level tools</p>';
        }
        echo '<a href="?module=pStructure">Manage dababase</a><br />';
        echo '<a href="?module=pEcran&action=editEcran">Manage screen</a><br />';
        echo '<a href="?module=pUser">Manage Users</a><br />';
    }
}
?>
