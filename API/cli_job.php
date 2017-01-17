<?php
/**
* Basic harness to ensure the script can only be loaded from the CLI
*/
if (php_sapi_name() != "cli") {
	echo ' php_sapi_name['.php_sapi_name().']'.PHP_EOL;
	exit('FaakUff');
} 

if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] != 'production') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

$APPLICATION_ROOT = dirname(dirname(__DIR__)).'/';
chdir($APPLICATION_ROOT);

if (file_exists('init.php')) {
    include 'init.php';
}else{
	die('
	application not initialized, your relative path<br>'.PHP_EOL.' 
	from your API to your base install has not been calculated correctly<br>'.PHP_EOL.'
	in the APPLICATION_ROOT variable
	');
}

/*
-t type CRON /CLI
-s sub type CRON: DAILY/HOURLY

*/
$params = array(
);
$DSN = 'JCORE';
$CLI =  new JCORE\SERVICE\CRON\CLI_HARNESS($DSN);
$callResult = $CLI->runJobs($params);
$serviceResponse[] = $callResult;



echo __METHOD__.__LINE__.'$serviceResponse<pre>['.var_export($serviceResponse, true).']</pre>'.PHP_EOL; 

		
?>