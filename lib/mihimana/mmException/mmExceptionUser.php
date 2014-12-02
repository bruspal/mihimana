<?php

/**
 * Class mmExceptionUSR
 * User exception : free to use exception
 */

class mmExceptionUser extends mmException{
    public function __construct($message = null, $code = null, $previous = null){
        if (is_null($code)) $code = -9999;
        if (is_null($message)) $code = "Empty user exception";
        parent::__construct($message, $code, $previous);
    }
} 