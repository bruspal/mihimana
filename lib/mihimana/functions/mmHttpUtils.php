<?php
function mmStatusNotFound() {
    header('HTTP/1.0 404 Not Found');
}

function mmStatusForbidden() {
   header('HTTP/1.0 403 Forbidden');
}

function mmStatusInternalError() {
    header('HTTP/1.0 500 Internal Error');
}

function mmStatusBadRequest() {
    header('HTTP/1.0 400 Bad Request');
}

function mmStatusUnauthorized() {
    header('HTTP/1.0 401 Unauthorized');
}
