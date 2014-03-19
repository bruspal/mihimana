<?php

/* ------------------------------------------------------------------------------
  -------------------------------------
  Mihimana : the visual PHP framework.
  Copyright (C) 2012-2014  Bruno Maffre
  contact@bmp-studio.com
  -------------------------------------

  -------------------------------------
  @package : lib
  @module: mmJSON
  @file : mmIndice.php
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
     * @param mixed $data data sended to the client, if null and $success = true the method will only send a simple {"success": true} message
     * @param boolean $success set status of the json message. TRUE it's a succesfull result, false otherwise
     * @param integer $errorCode error code
     * @param string $errorMessage error message
     */
    public static function sendJSON($data, $success = true, $errorCode = -9999, $errorMessage = 'Uncategorized error') {
        header('Content-Type: application/json');
        if ($success) {
            if ( ! is_null($data)) {
                echo json_encode(array('success' => true, 'data' => $data));
            } else {
                echo json_encode(array('success' => true));
            }
        } else {
            echo json_encode(array('success' => false, 'errorCode' => $errorCode, 'errorMessage' => $errorMessage));
        }
    }
    
    
}
