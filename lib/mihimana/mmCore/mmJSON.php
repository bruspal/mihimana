<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmCore
  @file : mmJSON.php
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
  ------------------------------------------------------------------------------ */
/**
 * This class deals with all JSON operations
 */
class mmJSON extends mmObject{
    /**
     * Send json message to the client. Content-type is automacally set to 'application/json'<br>
     * if $success is true, datas are sended otherwise errorCode and errorMessage are sended
     * @param mixed $data data sended to the client, if null and $success = true (the default values) the method will only send a simple {"success": true, "data": []} message
     * @param boolean $success set status of the json message. TRUE it's a succesfull result, false otherwise
     * @param integer $errorCode error code
     * @param string $errorMessage error message
     */
    public static function sendJSON($data = null, $success = true, $errorCode = -9999, $errorMessage = 'Uncategorized error') {
        mmOutputJson();
        echo self::encodeJson($data, $success, $errorCode, $errorMessage);
    }
    /**
     * Send JSONP message @see sendJSON
     * @param type $data
     * @param type $success
     * @param type $errorCode
     * @param type $errorMessage
     */
    public static function sendJSONP($data = null, $success = true, $errorCode = -9999, $errorMessage = 'Uncategorized error') {
        if ( ! empty($_GET['callback'])) {
            mmOutputJsonp();
            echo $_GET['callback'].' (';
            echo self::encodeJson($data, $success, $errorCode, $errorMessage);
            echo ');';
        } else {
            mmOutputJson();
            echo self::encodeJson(null, false, -9999, 'Appel a JSONP sans parametre calback');
        }
    }

    /*
     * Ensemble des fonctions d'erreur
     */

    /**
     * Envois une erreur 404, $message correspond au message a renvoyer par defaut "l'élément recherché est introuvable"
     * @param type $message
     */
    public static function sendNotFound($message="404 : Not found") {
        mmStatusNotFound();
        self::sendJSONError(mmExceptionHttp::NOT_FOUND, $message);
    }
    /**
     * Envois une erreur 403, $message correspond au message a renvoyer par defaut "Accès interdit"
     * @param type $message
     */
    public static function sendForbidden($message="403 : Forbidden") {
        mmStatusForbidden();
        self::sendJSONError(mmExceptionHttp::FORBIDDEN, $message);
    }

    /**
     * Envois une erreur 500, $message correspond au message a renvoyer par defaut "Erreur interne"
     * @param type $message
     */
    public static function sendInternalError($message="500 : Internal error") {
        mmStatusInternalError();
        self::sendJSONError(mmExceptionHttp::INTERNAL_ERROR, $message);
    }

    /**
     * Envois une erreur 400, $message correspond au message a renvoyer par defaut "Les informations reçut sont incorrectes"
     * @param type $message
     */
    public static function sendBadRequest($message="400 : Bad request") {
        mmStatusBadRequest();
        self::sendJSONError(mmExceptionHttp::BAD_REQUEST, $message);
    }

    /**
     * Envois une erreur 401, $message correspond au message a renvoyer par defaut "Les informations reçut sont incorrectes"
     * @param type $message
     */
    public static function sendUnauthorized($message="401 : Unauthorized") {
        mmStatusUnauthorized();
        self::sendJSONError(mmExceptionHttp::UNAUTHORIZED, $message);
    }

    /**
     * Send standardized JSON error message
     * @param type $errorCode error code
     * @param type $errorMessage error string
     */
    public static function sendJSONError($errorCode = -9999, $errorMessage = 'Uncategorized error') {
        self::sendJSON(null, false, $errorCode, $errorMessage);
    }

    /**
     * Send standardized JSON error message
     * @param type $errorCode error code
     * @param type $errorMessage error string
     */
    public static function sendJSONPError($errorCode = -9999, $errorMessage = 'Uncategorized error') {
        self::sendJSONP(null, false, $errorCode, $errorMessage);
    }

    /**
     * Send $data as JSON string
     * @param array $data
     */
    public static function sendRawJSON($data) {
        mmOutputJson();
        $data = (array)$data;
        echo json_encode($data);
    }

    /**
     * Send $data as JSONP string
     * @param array $data
     */
    public static function sendRawJSONP($data) {
        mmOutputJsonp();
        $data = (array)$data;
        echo $_GET['callback'].' (';
        echo json_encode($data);
        echo ');';

    }
    private static function encodeJson($data, $success, $errorCode, $errorMessage) {

        $data = self::utf8encode($data);

        //encode json regarding parameters
        if ($success) {
            if ( ! is_null($data)) {
                $json = json_encode(array('success' => true, 'data' => $data));
            } else {
                $json = json_encode(array('success' => true, 'data' => array()));
            }
        } else {
            $json = json_encode(array('success' => false, 'errorCode' => $errorCode, 'errorMessage' => $errorMessage));
        }
        //Check if well formed json and send it otherwise send feedback
        if ($json) return $json;    //everythings OK
        //here comes troubles
        if (DEBUG)
            return json_encode(array('success' => false, 'errorCode' =>  -9999, 'errorMessage' => 'JSON error :'.json_last_error().' : '.json_last_error_msg()));
        else
            return json_encode(array('success' => false, 'errorCode' =>  -9999, 'errorMessage' => 'JSON error :'.json_last_error()));
    }

    private function utf8encode ($data) {
        return $data;

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = utf8_encode($val);
                }
                if (is_array($val)) {
                    $data[$key] = self::utf8encode($val);
                }
            }
        }

        return $data;
    }
    /**
     * Return an associative array of a JSON post call
     * @return array an associative array of received data, return false if no data has been received.
     */
    public static function getPost() {
        if ($input = file_get_contents('php://input')) {
            $varHolder = new mmVarHolder(json_decode($input, true));
            return $varHolder;
        } else {
            return false;
        }
    }

}
