<?php
/* first of all we are checking if allready exists config file */
if (file_exists("config.inc")) {
    require 'appsmanager.php'; //run app manager
} else {
    require 'runme1st.php'; //first run after a fresh install. /!\ for now it's only a very simple module to create simple template
}
?>
