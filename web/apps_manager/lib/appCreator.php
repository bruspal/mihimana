<?php
/**
 * This function create a new application.
 * @param type $params app parameters 
 * @return boolean true => ok otherwise false
 */
function createApp($params) {
    // first of all use the sketch to create minimal needed files
    $appDir = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.$params['appname'];
    if (file_exists($appDir)) {
        if (empty($params['ovrwr'])) {
            echo "Application directory allready exists. Cannot overwrite existing app";
            return false;
        }
    } else {
        if ( ! mkdir($appDir)) { //create base directory
            echo "fail to create $appDir";
            return false;
        }
    }
    //recursively copy sketch to create directories and files needed for the new application.
    mmRecursiveCopy(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'sketch', $appDir);
    
    //change files content to fit with user parameters
    $config = file_get_contents($appDir.DIRECTORY_SEPARATOR.'config/config.php');
    if ($config === false) {
        echo "fail to read config file template";
        return false;
    }
    //update database parameters in the config file
    $database = $params['dbdriver'].'://';
    if ( ! empty($params['dbuser'])) {
        $database .= $params['dbuser'];
        if ( ! empty($params['dbpasswd'])) {
            $database .= ':'.$params['dbpasswd'].'@';
        }
    }
    $database .= $params['dbserv'].'/'.$params['dbname'];
    
    $config = str_replace('%database%', $database, $config);
    
    if (file_put_contents($appDir.DIRECTORY_SEPARATOR.'config/config.php', $config) === false) {
        echo "fail to write config file";
        return false;
    }
    
    //create the entry point which takes place in the web directory
    //change files content to fit with user parameters
    $index = file_get_contents(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'app_index.php');
    if ($index === false) {
        echo "fail to read entry file template";
        return false;
    }
    
    //do the substitution
    $index = str_replace(array('%application%', '%module%', '%action%'), array($params['appname'], $params['defmod'], $params['defact']), $index);
    $webdir = dirname(dirname(dirname(__FILE__)));
    
    if (file_put_contents($webdir.DIRECTORY_SEPARATOR.$params['appname'].'.php', $index) === false) {
        echo "fail to write entry file";
        return false;
    }
    
    //set permissions
    chmod($appDir, 0777);
    mmRecursiveChmod($appDir);
    
    return true;
    
}
?>
