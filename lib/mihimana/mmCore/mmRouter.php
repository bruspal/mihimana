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
@file : mmRouter.php
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

//le temps du dev
$route = array( // exemple de route pour tester
    '/login'    => '/:module=pLogin:/:action=login:',
    '/*'        => '/:module:/:action=index:',
    '/*/*'      => '/:module:/:action:',
    '/'         => '/:module=main:/:action=index:'
);

class mmRouter extends mmObject {
    // Attributes
    //Public
    //Protected
    protected $uriString = '';
    protected $uriArray = array();
    protected $module = '';
    protected $action = '';
    protected $extraParameters = '';
    
    
    //Methods
    //Public
    /**
     * Initialise the router system regarding rules and etc.
     * @param string $uri(Optional) if ommited the route will be calculated from the current context.
     */
    public function __construct($uri = '') {
        if (empty($uri)) {
            $this->getUriFromContext();
        } else {
            $this->uriString = $uri;
        }
        $this->explodeRoute();
    }
    
    public function getUriString() {
        
    }
    
    public function getUriArray() {
        
    }
    //Protected
    protected function getUriFromContext() {
        
        $reqUri = $_SERVER['REQUEST_URI'];
        
        if (strpos($reqUri, $_SERVER['SCRIPT_NAME']) === 0) { // Does the script name appear in the uri ?
            $reqUri = substr($reqUri, strlen($_SERVER['SCRIPT_NAME'])); // remove the scriptname
        } elseif (strpos($reqUri, dirname($_SERVER['SCRIPT_NAME'])) === 0) { // Does the script's containing directory appear in the uri ?
            $reqUri = substr($reqUri, strlen(dirname($_SERVER['SCRIPT_NAME']))); // remove it
        }
        
        $this->uriString = $reqUri;
    }
    
    protected function explodeRoute() {
        $this->uriArray = explode('/', $this->uriString);
        //parse_str($str, $arr) //POUR INFO : parse la chaine $str (param1=val1&param2=val2&...) et met le resultat dans le tableau associatif $arr
    }

}
