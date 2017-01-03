<?php

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
$CLI =  new JCORE\SERVICE\CRON\CLI_HARNESS('BLACKWATCH');
$callResult = $CLI->runJobs($params);
$serviceResponse[] = $callResult;



echo __METHOD__.__LINE__.'$serviceResponse<pre>['.var_export($serviceResponse, true).']</pre>'.PHP_EOL; 



#*/20 * * * * /usr/local/bin/php /var/www/vhosts/blackwatch_dev/APIS/CLI/cli_job.php -tCRON -sDAILY >> /var/www/vhosts/blackwatch_dev/APIS/CLI/cli_job.log 2>&1
/*

SERVICE\ADVERTISER\WEBSITE.getTheWalkingUnDUG
*/

		
		
?>