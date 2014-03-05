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
    protected $cleanedReqUri = '';
    protected $module = '';
    protected $action = '';
    protected $getRequest = array();
    protected $postRequest = array();
    protected $requestArray = array();
    
    
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
        $this->parseRoute();
    }
    
    public function getUriString() {

    }
    
    public function getUriArray() {
        
    }
    
    public function getRequest() {
        $request = new mmRequest($this->requestArray);
        return $request;
    }
    
    public function getRequestArray() {
        return $this->requestArray;
    }
    //Protected
    protected function getUriFromContext() {
        
        $reqUri = $_SERVER['REQUEST_URI'];
        //URI cleaning
        if (strpos($reqUri, $_SERVER['SCRIPT_NAME']) === 0) { // Does the script name appear in the uri ?
            $reqUri = substr($reqUri, strlen($_SERVER['SCRIPT_NAME'])); // remove the scriptname
        } elseif (strpos($reqUri, dirname($_SERVER['SCRIPT_NAME'])) === 0) { // Does the script's containing directory appear in the uri ?
            $reqUri = substr($reqUri, strlen(dirname($_SERVER['SCRIPT_NAME']))); // remove it
        }
        //cleaning start of string
        while(strncmp('?', $reqUri, 1) == 0 || strncmp('/', $reqUri, 1) == 0) {
            $reqUri = substr($reqUri, 1);
        }
        
        $this->uriString = $reqUri;
    }
    
    protected function explodeRoute() {
        if ( ! empty($this->uriString)) { //there is somethinf to parse ?
            //first doing separation between uri and parameters
            $fragments = explode('?', $this->uriString);
            $this->cleanedReqUri = $fragments[0];
            $this->uriArray = explode('/', $fragments[0]);
            $this->getRequest = $_GET;
            $this->postRequest = $_POST;
        }
    }
    
    protected function parseRoute() {
        $_routes = array( //default routes for generique purpose
            'login'     =>  'module=pLoginStd&action=login',
            'logout'    =>  'module=pLoginStd&action=logout',
            'subscribe' =>  'module=pLoginStd&action=subscribe',
            'user/*'    =>  'module=profile&action=viewUser&userName=$1',
            'user'      =>  'module=profile&action=index',
            '*/*'       =>  'module=$1&action=$2',
            '*'         =>  'module=$1&action=index',
        );
        $routesFile = APPLICATION_CONFIG_DIR.DIRECTORY_SEPARATOR.'routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
            $_routes = array_merge($_routes, $routes);
        }
        // first check empty uri
        if (empty($this->cleanedReqUri)) {
            $this->requestArray = array_merge($this->getRequest, $this->postRequest);
            return true;
        }
        
        // check if exists direct routes
        if (isset($_routes[$this->cleanedReqUri])) {
            parse_str($_routes[$this->cleanedReqUri], $this->requestArray);
            $this->requestArray = array_merge($this->requestArray, $this->getRequest, $this->postRequest);
            return true;
        }
        //then use regular expression to find
        foreach ($_routes as $pattern => $subject) {
            $pattern = str_replace('*', '(.+)', $pattern);
            if (preg_match('#'.$pattern.'#', $this->cleanedReqUri, $matches) == 1) { // une des routes satisfait la condition
                if (strpos($subject, '$') != 0) { // doit on substituer ?
                    //on fait les substitutions pour chaque occurence de $matches. On saute l'indice 0
                    for ($i = 1; $i < count($matches); $i++) {
                        $subject = str_replace('$'.$i, $matches[$i], $subject);
                    }
                    parse_str($subject, $this->requestArray);
                    $this->requestArray = array_merge($this->requestArray, $this->getRequest, $this->postRequest);
                    return true;
                }
            }
        }
    }
    
    
}
