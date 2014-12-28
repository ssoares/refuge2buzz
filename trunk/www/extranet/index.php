<?php
// ensure that the errors appear
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);
//error_reporting(E_ALL | E_STRICT);
header('X-UA-Compatible: IE=8');
ini_set('display_errors', true);
ini_set('magic_quotes_gpc', 'off');

// define the timezone
date_default_timezone_set('Canada/Eastern');
setlocale(LC_CTYPE, 'fr_CA.utf8');
$rootDir = dirname(dirname(dirname(__FILE__)));
$application_path = "{$rootDir}/application";
$extranet_path = "{$rootDir}/extranet/";
$lib_path = "{$rootDir}/lib";

// loading configuration
$host = explode('.', $_SERVER['HTTP_HOST']);
$envVar = $host[0];
if (in_array('sandboxes', $host) || in_array('localhost', $host))
{
    $devUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $envVar = $devUri[1] . '-dev';
}
elseif (in_array('dev', $host)||in_array('devld', $host)){
    $envVar = str_replace(array('-fr', '-portail2013', '-cp'), array('', '', ''), $host[0]) . '-dev';
    define('ISDEV', true);
}
elseif (in_array('staging', $host))
    $envVar = str_replace(array('csss-iugs'), array('c3s'), $host[0]) . '-staging';
else
    $envVar = str_replace(array('suitedonna'), array('donna'), $host[0]);

define('APPLICATION_ENV', $envVar);
define('APPLICATION_PATH', $rootDir);
define("WURFL_DIR", $lib_path . '/Wurfl/'); // WURFL INSTALLATION DIR
define("RESOURCES_DIR", $rootDir . "/cache/wurfl/"); // DIRECTORY WHERE YOU PUT YOUR CONFIGURATION FILES
/**
 * Path to the GUI for the back office
 */
define('FRONTEND', $extranet_path);

// setting up directories and loading of classes
set_include_path('.'
    . PATH_SEPARATOR . "$rootDir/lib"
    . PATH_SEPARATOR . "$rootDir/lib/Cible/Models/"
    . PATH_SEPARATOR . FRONTEND . "modules/profile/models/"
    . PATH_SEPARATOR . FRONTEND . "modules/users/models/"
    . PATH_SEPARATOR . FRONTEND . "modules/utilities/models/"
    . PATH_SEPARATOR . $lib_path . '/Wurfl/'
    . PATH_SEPARATOR . $rootDir . "/cache/wurfl/"
    . PATH_SEPARATOR . FRONTEND . "includes"
    . PATH_SEPARATOR . get_include_path());

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

require_once 'Zend/Application.php';

$application = new Zend_Application(
        APPLICATION_ENV,
        array(
            'bootstrap' => array(
                'class' => 'Bootstrap_Multidb',
                'path' => $lib_path . '/Cible/Bootstrap/Multidb.php',
            ),
            'config' => $application_path . '/config.ini',
        ),
        array(
            'pluginPaths' => array(
                'Cible_Plugin_Cron' => $lib_path . '/Cible/Plugins/Resource'
            )
        )
);
$application->bootstrap()->run();

// Clear all variables so that they don't get in the global scope
unset($rootDir, $application_path, $extranet_path, $lib_path);
