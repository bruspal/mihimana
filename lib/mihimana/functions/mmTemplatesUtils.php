<?php
/**
 * return parsed partial string
 * @param string $templateName template name without extension. partials are located in APPLICATION_DIR/templates/partials with the .php extension by default
 * @param array $variable array of variables ('varName' => 'value, ...). Default: array()
 * @param string $extension file's extension. default: .php 
 * @return string
 */
function getPartial($templateName, $variables = array(), $extension = '.php') {
    $strTemplate = mmTemplate::renderTemplate($templateName.$extension, $variables, getPartialPath());
    return $strTemplate;
}
/**
 * echo parsed partial
 * @param string $templateName template name without extension. partials are located in APPLICATION_DIR/templates/partials with the .php extension by default
 * @param array $variable array of variables ('varName' => 'value, ...). Default: array()
 * @param string $extension file's extension. default: .php 
 * @return string
 */
function renderPartial($templateName, $variables = array(), $extension = '.php') {
    echo getPartial($templateName, $variables, $extension);
}
