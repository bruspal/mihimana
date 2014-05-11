<?php
/* App metatag
 * #AppName: %applicaion%#
 * 
 */

//config par defaut pour cette application et configuration de mihimana
define('APPLICATION', 'welcomeApp'); //definie le nom de l'application et le repertoire dans laquel se trouve les programmes
define('MODULE_DEFAUT', 'main');  //Programme a lancer par defaut si 'module' n'est pas fournis
define('ACTION_DEFAUT', 'index'); //action par defaut si elle est pas donnée en parametre
define('DEBUG', true); // met le programme en mode debug ou non
define('MODE_INSTALL', true); // mis a vrai on passe en mode installation d'une nouvelle application /!\ ce mode est tres dangereux car plus de sécurité du tout
define('NO_LOGIN', true); //Il n'y a pas de demande de login l'utilisateur est automatique connecté et recoit le numero d'utilisateur 1
define('SUPER_ADMIN', true); //pris en compte que NO_LOGIN est a true. a true, l'utilisateur est considéré comme super admin
//On demarre mihimana
require_once '../lib/mihimana/mihimana.php';

?>
