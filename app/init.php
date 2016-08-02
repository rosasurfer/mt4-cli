<?php
if (PHP_VERSION < '5.6') {
   echo('application error'.PHP_EOL);
   error_log('Error: A PHP version >= 5.6 is required (found version '.PHP_VERSION.').');
   exit(1);
}


// check application constants
!defined('APPLICATION_ROOT') && define('APPLICATION_ROOT', dirname(__DIR__));
!defined('APPLICATION_ID'  ) && define('APPLICATION_ID',  'myfx');


// global settings
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('error_log', APPLICATION_ROOT.'/etc/log/php_error.log');
!isSet($_SERVER['REQUEST_METHOD']) && set_time_limit(0);          // no time limit for CLI


// load Ministruts and project definitions
require(APPLICATION_ROOT.'/etc/vendor/rosasurfer/ministruts/src/load-global.php');
include(APPLICATION_ROOT.'/app/defines.php');


// TODO: replace with case-insensitive loader
use phalcon\Loader as ClassLoader;
(new ClassLoader())->registerClasses(include(__DIR__.'/classmap.php'))
                   ->register();


/**
 * Alias für MyFX::fxtTime()
 *
 * @param  int    $time       - Timestamp (default: aktuelle Zeit)
 * @param  string $timezoneId - Timezone-Identifier des Timestamps (default: GMT=Unix-Timestamp).
 *
 * @return int - FXT-Timestamp
 *
 * @see    MyFX::fxtTime()
 */
function fxtTime($time=null, $timezoneId=null) {
   if (func_num_args() <= 1)
      return MyFX::fxtTime($time);
   return MyFX::fxtTime($time, $timezoneId);
}


/**
 * Alias für MyFX::fxtDate()
 *
 * Formatiert einen Zeitpunkt als FXT-Zeit.
 *
 * @param  int    $timestamp - Zeitpunkt (default: aktuelle Zeit)
 * @param  string $format    - Formatstring (default: 'Y-m-d H:i:s')
 *
 * @return string - FXT-String
 *
 * Analogous to the date() function except that the time returned is Forex Time (FXT).
 */
function fxtDate($time=null, $format='Y-m-d H:i:s') {
   return MyFX::fxtDate($time, $format);
}
