<?php
/**
 * Very basic CRON mechanism 
 * 
 * @author	Jason Medland<jason.medland@gmail.com>
 * @package	JCORE\SERVICE\CRON 
 */

namespace JCORE\SERVICE\CRON;
#use JCORE\TRANSPORT\SOA\SOA_BASE as SOA_BASE; //if it extends SOA_BASE it should be a a *.service.php class
#use JCORE\DAO\DAO as DAO;
#use JCORE\AUTH\AUTH_INTERFACE as AUTH_INTERFACE;

use JCORE\TRANSPORT\SOA\SERVICE_VALIDATOR as SERVICE_VALIDATOR;
/**
 * Class CLI
 *
 * @package JCORE\SERVICE\CRON 
*/
class CLI_HARNESS{ 
	/**
	* serviceRequest
	* 
	* @access protected 
	* @var string
	*/
	protected $serviceRequest = null;
	/**
	* logTable
	* 
	* @access protected 
	* @var string
	*/
	protected $logTable = 'cron_log';
	
	/**
	* error
	* 
	* @access public 
	* @var string
	*/
	public $serviceResponse = null;
	/**
	* error
	* 
	* @access public 
	* @var string
	*/
	public $error = null;
	/**
	* optionsDefault
	* 
	* @access public 
	* @var string
	*/
	public $optionsDefault = "t:s:p:";
	/**
	* options
	* 
	* @access public 
	* @var string
	*/
	public $options = "t:s:p:";
	/**
	* TYPE
	* 
	* @access public 
	* @var string
	*/
	public $TYPE = "CRON";
	/**
	* SUB_TYPE
	* 
	* @access public 
	* @var string
	*/
	public $SUB_TYPE = "DAILY";
	/**
	* JOBLIST
	* 
	* @access public 
	* @var array
	*/
	public $JOBLIST = array();
	
	/**
	* JOBLIST
	* 
	* @access public 
	* @var array
	*/
	public $errors = array();
	
	/**
	* DESCRIPTOR: an empty constructor, the service MUST be called with 
	* the service name and the service method name specified in the 
	* in the method property of the JSONRPC request in this format
	* 		""method":"AJAX_STUB.aServiceMethod"
	* 
	* 
	* @access public 
	* @param string DSN
	* @return null
	*/
	public function __construct($DSN=null){
		if(null !== $DSN){
			$this->DSN = $DSN;
		}else{
			$this->DSN = 'JCORE';
		}
			
		
		return;
	}
	/**
	* initialize the harness
	*
	* 
	* @access public 
	* @param array args
	* @return null
	*/
	public function init($args=null){
		if (php_sapi_name() != "cli") {
			#echo ' php_sapi_name['.php_sapi_name().']'.PHP_EOL;
			exit('FaakUff');
		}
		#$options = getopt("t:s:p:");
		#echo __METHOD__.__LINE__.'getopt()<pre>['.var_export(getopt(), true).']</pre>'.'<br>';
		/**
		if(!isset($args["options"]) || '' == $args["options"]){
			$this->options = getopt("t:s:p:"); 
		}else{
			$this->options = getopt($args["options"]); 
		}

		if(isset($this->options["t"])){
			$this->TYPE = $this->options["t"];
		}

		if(isset($this->options["s"])){
			$this->SUB_TYPE = $this->options["s"];
		}
		*/
		
		$this->JOBLIST = $GLOBALS["CONFIG_MANAGER"]->getSetting('CLI_JOBS',"CRON");
		if(!is_array($this->JOBLIST) || 0 == count($this->JOBLIST)){
			exit (' INVALID JOB TYPE['.$this->TYPE.'] OR SUB_TYPE['.$this->SUB_TYPE.']'); 
		}
		
		
		return;
		
	}
	
	/**
	* DESCRIPTOR: lastJob
	* lookup the last job
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function lastJob($params = null){
		//$this->init($params);
		#echo __METHOD__.__LINE__.'params<pre>['.var_export($params, true).']</pre>'.'<br>'; 
		if(0 == count($this->JOBLIST)){
			$this->errors[__METHOD__][] = 'no jobs to do';
			return false;
		}
		
		#echo __METHOD__.__LINE__.'this->last_run_check_time ['.$this->last_run_check_time.'] - $this->current_time ['.$this->current_time.']'.PHP_EOL; 
		
		$query = '
		SELECT last_initialization, job_duration
		FROM cron_log
		WHERE job_name = "'.$params["JOB_NAME"].'"
		AND last_initialization BETWEEN "'.$this->last_run_check_time.'" AND "'.$this->current_time.'"
		ORDER BY last_initialization DESC
		';
		/**
		AND job_duration IS NOT NULL 
		AND job_duration != "" 
		*/
		#echo __METHOD__.'@'.__LINE__.' query<pre>['.var_export($query, true).']</pre> '.'<br>'.PHP_EOL;
		$result = $GLOBALS["DATA_API"]->retrieve($this->DSN, $query, $extArgs=array('returnArray' => true));
		#echo __METHOD__.'@'.__LINE__.' result<pre>['.var_export($result, true).']</pre> '.'<br>'.PHP_EOL;
		
		return $result;
	}
	
	/**
	* DESCRIPTOR: logJob
	* do the audit trail thing
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function logJob($params = null){
		//$this->init($params);
		#echo __METHOD__.__LINE__.'params:['.var_export($params, true).']'.PHP_EOL; 
		#echo __METHOD__.__LINE__.'this->last_run_check_time ['.$this->last_run_check_time.'] - $this->current_time ['.$this->current_time.']'.PHP_EOL; 
		if(!is_scalar($params["service_call_params"])){
			#echo __METHOD__.__LINE__.'JSON ENCODE service_call_params['.$params["service_call_params"].']'.PHP_EOL; 
			$params["service_call_params"] = json_encode($params["service_call_params"]);
		}
		#echo __METHOD__.__LINE__.'JSON ENCODE service_call_params['.$params["service_call_params"].']'.PHP_EOL; 

		$query = "
		INSERT INTO cron_log (
			job_name, 
			service_call, 
			service_call_params, 
			last_initialization
		)
		VALUES (
			'".$params["job_name"]."',
			'".$params["service_call"]."',
			'".$params["service_call_params"]."',
			'".$this->current_time."'
		);
		";
		
		
		
		#echo __METHOD__.'@'.__LINE__.' query:['.var_export($query, true).']'.PHP_EOL; 
		$result = $GLOBALS["DATA_API"]->create($this->DSN, $query, $extArgs=array('returnArray' => true));
		$this->log_id = 0;
		if(isset($result["INSERT_ID"])){
			$this->log_id = $result["INSERT_ID"];
		}else{
			$this->errors[__METHOD__][] = 'query failed for job_name '.$params["job_name"].'  ';
			#echo __METHOD__.'@'.__LINE__.' result:['.var_export($result, true).']'.PHP_EOL; 
		}		
		return $result;
	}
	
	/**
	* DESCRIPTOR: updateLogJob
	* mark the job as and when complete
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function updateLogJob($params = null){
		//$this->init($params);
		#echo __METHOD__.__LINE__.'params<pre>['.var_export($params, true).']</pre>'.'<br>'; 

		

		#$job_duration = mktime(date("H"),date("i"),date("s")+1,date("n"),date("j"),date("Y")) - $this->current_timestamp;
		#echo __METHOD__.__LINE__.'$job_duration['.$job_duration.'] ['.date("H:i:s",$job_duration).']'.PHP_EOL; 
		$datetime1 = date_create($this->current_time);
		$datetime2 = date_create(date("Y-m-d H:i:s",mktime(date("H"),date("i"),date("s")+1,date("n"),date("j"),date("Y"))));
		$interval = date_diff($datetime1, $datetime2);
		$job_duration = $interval->format("%H:%I:%S");
		#echo '$interval->format ['.$interval->format("%H:%I:%S").']'.PHP_EOL;
		
		#echo __METHOD__.__LINE__.'$job_duration['.$job_duration.'] '.PHP_EOL; 
		$this->current_timestamp = mktime(date("H"),date("i"),date("s"),date("n"),date("j"),date("Y"));
		$query = "
		UPDATE cron_log 
		SET 
			service_call_result_summary = '".json_encode($params["service_call_result_summary"])."',
			service_call_result_data = '".json_encode($params["service_call_result_data"])."',
			job_duration = '".$job_duration."'
		WHERE cron_log_pk = ".$this->log_id."
		";
	
		
		#echo __METHOD__.'@'.__LINE__.' query<pre>['.var_export($query, true).']</pre> '.'<br>'.PHP_EOL;
		$result = $GLOBALS["DATA_API"]->update($this->DSN, $query, $extArgs=array('returnArray' => true));
		#echo __METHOD__.'@'.__LINE__.' result<pre>['.var_export($result, true).']</pre> '.'<br>'.PHP_EOL;
		
		return $result;
	}
	/**
	* DESCRIPTOR: getJobs 
	* look for a job list and return if not empty
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function getJobs($params = null){
		$this->init($params);
		if(0 == count($this->JOBLIST)){
			$this->errors[__METHOD__][] = 'no job list ';
			return false;
		}
		foreach($this->JOBLIST as $key => $value ){
			#echo __METHOD__.__LINE__.'$key['.$key.'] $value["JOB_NAME"]['.$value["JOB_NAME"].'] '.PHP_EOL; 
			#echo __METHOD__.__LINE__.'$key['.$key.']<pre>['.var_export($value, true).']</pre>'.PHP_EOL; 
			#echo __METHOD__.__LINE__.'$key['.$key.'] '.PHP_EOL; 
			
			$callResult = '';
			$result = array();
			$errors = array();
			

			$mktimeArgs = array(
				date("H"), # 'hour'  => 0
				date("i"), # 'minute'   => 1
				date("s"), # 'second'  => 2
				date("n"), # 'month'  => 3
				date("j"), # 'day'  => 4
				date("Y"), # 'year'  => 5
			);
			$this->current_time = date("Y-m-d H:i:s");
			$this->current_timestamp = mktime($mktimeArgs[0],$mktimeArgs[1],$mktimeArgs[2],$mktimeArgs[3],$mktimeArgs[4],$mktimeArgs[5]);
			

			switch($value["ITERATION_UNIT"]){
				case"H": #hour
					$mktimeArgs[0] = date("H")- $value["ITERATIONS"];
					#echo ' date("H")['.date("H").'] $value["ITERATIONS"] ['.$value["ITERATIONS"].'] $mktimeArgs[0]['.$mktimeArgs[0].']'.PHP_EOL;
					break;
				case"i": #minute
					$mktimeArgs[1] = date("i")- $value["ITERATIONS"];
					#echo ' date("i")['.date("i").'] $value["ITERATIONS"] ['.$value["ITERATIONS"].'] $mktimeArgs[1]['.$mktimeArgs[1].']'.PHP_EOL;
					break;
				case"s": #second
					$mktimeArgs[2] = date("s")- $value["ITERATIONS"];
					break;
				case"n": #month
					$mktimeArgs[3] = date("n")- $value["ITERATIONS"];
					break;
				case"j": #day
					$mktimeArgs[4] = date("j")- $value["ITERATIONS"];
					break;
				case"Y": #year
					$mktimeArgs[5] = date("Y")- $value["ITERATIONS"];
					break;
				default:
					break;
			}

			$this->last_run_check_time = date("Y-m-d H:i:s", mktime($mktimeArgs[0],$mktimeArgs[1],$mktimeArgs[2],$mktimeArgs[3],$mktimeArgs[4],$mktimeArgs[5]));
			#echo __METHOD__.__LINE__.'this->last_run_check_time ['.$this->last_run_check_time.'] - $this->current_time ['.$this->current_time.']'.PHP_EOL; 
			
			
			$lastJob = $this->lastJob($value);
			#echo __METHOD__.'@'.__LINE__.' lastJob ['.$value["JOB_NAME"].']<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
			if(isset($lastJob[0]["job_duration"]) && '' == $lastJob[0]["job_duration"]){
				$errors[] = 'unfinished job';
				#echo __METHOD__.'@'.__LINE__.'LAST JOB IS INCOMLETE lastJob<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
			}
			$logJobParams = array(
				"service_call" => $value["SERVICE_CALL"],
				"service_call_params" => $value["SERVICE_CALL_PARAMS"],
				"job_name" => $value["JOB_NAME"],
				"lastJob" => $lastJob[0],
				"current_time" => $this->current_time,
				"last_run_check_time" => $this->last_run_check_time,
			);
			

			if(0 == count($lastJob)){
				
				
				/**
				#echo __METHOD__.'@'.__LINE__.'READY TO GO lastJob<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
				$this->logJob($logJobParams);
				
				$serviceTest = SERVICE_VALIDATOR::validateServiceCall($value["SERVICE_CALL"]);
				$serviceObject = new $serviceTest['object']();
				$callResult = $serviceObject->$serviceTest['method']($value["SERVICE_CALL_PARAMS"]);;
				if(!isset($callResult["data"])){
					#echo __FILE__.__LINE__.' **** NO $callResult["data"] $value["SERVICE_CALL"]['.$value["SERVICE_CALL"].'] value["SERVICE_CALL_PARAMS"]<pre>['.var_export($value["SERVICE_CALL_PARAMS"], true).']</pre>'.PHP_EOL; 
					$errors[] = 'no data from call';
				}					
					echo __FILE__.__LINE__.' **** NO $callResult["data"] value<pre>['.var_export($value, true).']</pre> callResult<pre>['.var_export($callResult, true).']</pre>'.PHP_EOL; 
				$result["job_name"] = $value["JOB_NAME"];
				$result["job_description"] = $value["JOB_DESCRIPTION"];
				$result["service_call"] = $value["SERVICE_CALL"];
				$result["service_call_params"] = $value["SERVICE_CALL_PARAMS"];
				
				$result["status"] = $callResult["status"];
				$result["msg"] = $callResult["info"]["msg"];
				#$result["data"] = $callResult["info"]["data"];
				#$result["job_duration"] = $callResult["JOB_NAME"];
				
				$logJobParams["service_call_result_summary"] = $callResult["info"]["msg"];
				$logJobParams["service_call_result_data"] = $callResult["data"];
				
				$this->updateLogJob($logJobParams);
				#$result[] = $callResult;
				*/
			}elseif(count($lastJob) >1 ){
				
				$errors = 'Job jailed to log its last entry and exit points ';
				/*
				echo '************************multiple unfinished jobs********************************************';
				
				$this->logJob($logJobParams);
				$logJobParams["service_call_result_summary"] = array('multiple unfinished jobs');
				$logJobParams["service_call_result_data"] = $lastJob;
				
				$this->updateLogJob($logJobParams);
				*/
				
			}
			#exit;
			$this->errors["errors"] = $errors;
			$serviceResponse[$value["JOB_NAME"]] = $logJobParams;
			
			
		}
		
		$serviceResponse["errors"] = $this->errors;
		return $serviceResponse;
	}	
	
	/**
	* DESCRIPTOR: runJobs 
	* look for a job list and run if not empty
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function runJobs($params = null){
		$this->init($params);
		if(0 == count($this->JOBLIST)){
			$this->errors[__METHOD__][] = 'no job list ';
			return false;
		}
		foreach($this->JOBLIST as $key => $value ){
			#echo __METHOD__.__LINE__.'$key['.$key.'] $value["JOB_NAME"]['.$value["JOB_NAME"].'] '.PHP_EOL; 
			#echo __METHOD__.__LINE__.'$key['.$key.']<pre>['.var_export($value, true).']</pre>'.PHP_EOL; 
			#echo __METHOD__.__LINE__.'$key['.$key.'] '.PHP_EOL; 
			
			$callResult = '';
			$result = array();
			$errors = array();
			

			$mktimeArgs = array(
				date("H"), # 'hour'  => 0
				date("i"), # 'minute'   => 1
				date("s"), # 'second'  => 2
				date("n"), # 'month'  => 3
				date("j"), # 'day'  => 4
				date("Y"), # 'year'  => 5
			);
			$this->current_time = date("Y-m-d H:i:s");
			$this->current_timestamp = mktime($mktimeArgs[0],$mktimeArgs[1],$mktimeArgs[2],$mktimeArgs[3],$mktimeArgs[4],$mktimeArgs[5]);
			

			switch($value["ITERATION_UNIT"]){
				case"H": #hour
					$mktimeArgs[0] = date("H")- $value["ITERATIONS"];
					#echo ' date("H")['.date("H").'] $value["ITERATIONS"] ['.$value["ITERATIONS"].'] $mktimeArgs[0]['.$mktimeArgs[0].']'.PHP_EOL;
					break;
				case"i": #minute
					$mktimeArgs[1] = date("i")- $value["ITERATIONS"];
					#echo ' date("i")['.date("i").'] $value["ITERATIONS"] ['.$value["ITERATIONS"].'] $mktimeArgs[1]['.$mktimeArgs[1].']'.PHP_EOL;
					break;
				case"s": #second
					$mktimeArgs[2] = date("s")- $value["ITERATIONS"];
					break;
				case"n": #month
					$mktimeArgs[3] = date("n")- $value["ITERATIONS"];
					break;
				case"j": #day
					$mktimeArgs[4] = date("j")- $value["ITERATIONS"];
					break;
				case"Y": #year
					$mktimeArgs[5] = date("Y")- $value["ITERATIONS"];
					break;
				default:
					break;
			}

			$this->last_run_check_time = date("Y-m-d H:i:s", mktime($mktimeArgs[0],$mktimeArgs[1],$mktimeArgs[2],$mktimeArgs[3],$mktimeArgs[4],$mktimeArgs[5]));
			#echo __METHOD__.__LINE__.'this->last_run_check_time ['.$this->last_run_check_time.'] - $this->current_time ['.$this->current_time.']'.PHP_EOL; 
			
			
			$lastJob = $this->lastJob($value);
			#echo __METHOD__.'@'.__LINE__.' lastJob ['.$value["JOB_NAME"].']<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
			if(isset($lastJob[0]["job_duration"]) && '' == $lastJob[0]["job_duration"]){
				$errors[] = 'unfinished job';
				#echo __METHOD__.'@'.__LINE__.'LAST JOB IS INCOMLETE lastJob<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
			}
			$logJobParams = array(
				"service_call" => $value["SERVICE_CALL"],
				"service_call_params" => $value["SERVICE_CALL_PARAMS"],
				"job_name" => $value["JOB_NAME"],
			);
			

			if(0 == count($lastJob)){
				
				
				#echo __METHOD__.'@'.__LINE__.'READY TO GO lastJob<pre>['.var_export($lastJob, true).']</pre> '.'<br>'.PHP_EOL;
				$this->logJob($logJobParams);
				
				$serviceTest = SERVICE_VALIDATOR::validateServiceCall($value["SERVICE_CALL"]);
				$serviceObject = new $serviceTest['object']();
				$callResult = $serviceObject->$serviceTest['method']($value["SERVICE_CALL_PARAMS"]);;
				if(!isset($callResult["data"])){
					#echo __FILE__.__LINE__.' **** NO $callResult["data"] $value["SERVICE_CALL"]['.$value["SERVICE_CALL"].'] value["SERVICE_CALL_PARAMS"]<pre>['.var_export($value["SERVICE_CALL_PARAMS"], true).']</pre>'.PHP_EOL; 
					$errors[] = 'no data from call';
				}					
					#echo __FILE__.__LINE__.' **** NO $callResult["data"] value<pre>['.var_export($value, true).']</pre> callResult<pre>['.var_export($callResult, true).']</pre>'.PHP_EOL; 
				/**
				$result["job_name"] = $value["JOB_NAME"];
				$result["job_description"] = $value["JOB_DESCRIPTION"];
				$result["service_call"] = $value["SERVICE_CALL"];
				*/
				$result["service_call_params"] = $value["SERVICE_CALL_PARAMS"];
				
				$result["status"] = $callResult["status"];
				$result["msg"] = $callResult["info"]["msg"];
				#$result["data"] = $callResult["info"]["data"];
				#$result["job_duration"] = $callResult["JOB_NAME"];
				
				$logJobParams["service_call_result_summary"] = $callResult["info"]["msg"];
				$logJobParams["service_call_result_data"] = $callResult["data"];
				
				$this->updateLogJob($logJobParams);
				#$result[] = $callResult;
			}elseif(count($lastJob) >1 ){
				
				$errors = 'Job jailed to log its last entry and exit points ';
				/*
				echo '************************multiple unfinished jobs********************************************';
				
				*/
				$this->logJob($logJobParams);
				$logJobParams["service_call_result_summary"] = array('multiple unfinished jobs');
				$logJobParams["service_call_result_data"] = $lastJob;
				
				$this->updateLogJob($logJobParams);
				
			}
			#exit;
			$result["errors"] = $errors;
			$serviceResponse[$value["JOB_NAME"]] = $result;
			
			
		}
		$serviceResponse["errors"] = $this->errors;
		return $serviceResponse;
	}
	/**
	* DESCRIPTOR: runJob
	* look for a job list and run if not empty
	* 
	* @access public 
	* @param array params
	* @return null
	*/
	public function runJob($params = null){
		echo __METHOD__.'@'.__LINE__.'params<pre>['.var_export($params, true).']</pre> '.'<br>'.PHP_EOL;
		$this->init($params);
		$this->current_time = date("Y-m-d H:i:s");
		#echo __METHOD__.'@'.__LINE__.'this->JOBLIST<pre>['.var_export($this->JOBLIST, true).']</pre> '.'<br>'.PHP_EOL;
		if(null !== $params && isset($this->JOBLIST[$params])){
			echo __METHOD__.'@'.__LINE__.' SET DEFAULT'.'<br>'.PHP_EOL;
			$value = array(
				'SERVICE_CALL' => $this->JOBLIST[$params]["SERVICE_CALL"],
				'SERVICE_CALL_PARAMS' => $this->JOBLIST[$params]["SERVICE_CALL_PARAMS"],
			);
		}
		echo __METHOD__.'@'.__LINE__.'value<pre>['.var_export($value, true).']</pre> '.'<br>'.PHP_EOL;
		#echo __METHOD__.'@'.__LINE__.'   $this->JOBLIST["AV1_IP_SCAN"]["SERVICE_CALL"]  ['.$this->JOBLIST["AV1_IP_SCAN"]["SERVICE_CALL"].']'.'<br>'.PHP_EOL;
		echo __METHOD__.'@'.__LINE__.'   $this->JOBLIST["AV1_IP_SCAN"]["SERVICE_CALL_PARAMS"]  ['.var_export($this->JOBLIST["AV1_IP_SCAN"]["SERVICE_CALL_PARAMS"], true).']'.'<br>'.PHP_EOL;
		if(0 == count($this->JOBLIST)){
			$this->errors[__METHOD__][] = 'no job list ';
			return false;
		}
		
		$logJobParams = array(
			"service_call" => $value["SERVICE_CALL"],
			"service_call_params" => $value["SERVICE_CALL_PARAMS"],
			"job_name" => $params,
		);
		echo __METHOD__.'@'.__LINE__.'logJobParams<pre>['.var_export($logJobParams, true).']</pre> '.'<br>'.PHP_EOL;
		#exit;
		$this->logJob($logJobParams);
		
		$serviceTest = SERVICE_VALIDATOR::validateServiceCall($value["SERVICE_CALL"]);
		$serviceObject = new $serviceTest['object']();
		$callResult = $serviceObject->$serviceTest['method']($value["SERVICE_CALL_PARAMS"]);;
		if(!isset($callResult["data"])){
			#echo __FILE__.__LINE__.' **** NO $callResult["data"] $value["SERVICE_CALL"]['.$value["SERVICE_CALL"].'] value["SERVICE_CALL_PARAMS"]<pre>['.var_export($value["SERVICE_CALL_PARAMS"], true).']</pre>'.PHP_EOL; 
			$this->errors[] = 'no data from call';
		}	
		$logJobParams["service_call_result_summary"] = $callResult["info"]["msg"];
		$logJobParams["service_call_result_data"] = $callResult["data"];
		
		$this->updateLogJob($logJobParams);
			
		
		$serviceResponse[$params] = $callResult;
			
			
		
		$serviceResponse["errors"] = $this->errors;
		return $serviceResponse;
	}
}



?>