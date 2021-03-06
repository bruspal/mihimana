<?php
/*
 * $credentials array allows to define anonymous access per module and per module/action
 *  * all actions in module : 'module_name' => false | true
 *  * for an action in a module : 'module_name/action_name' => false | true
 * 
 * false: no signin required
 * true: signin required
 * 
 * /!\ First module/action is checked then module. Finally if none off previous check succeed credentials is set to true
 */
$credentials = array(
    'MODULE/ACTION'          => false,  //allows access to MODULE/ACTION
    'MODULE'                 => false,  //allows access to all actions of MODULE
);