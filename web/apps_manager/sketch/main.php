<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class main extends mmProg {
    public function executeIndex(mmRequest $request) {
        echo "<h1>Welcome to your brand new application</h1>";
        echo '<a href="?module=pStructure">Manage dababase</a><br />';
        echo '<a href="?module=pEcran&action=editEcran">Manage screen</a><br />';
        echo '<a href="?module=pUser">Manage Users</a><br />';
    }
}
?>
