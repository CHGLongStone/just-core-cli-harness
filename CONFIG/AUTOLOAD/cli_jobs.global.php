<?php 
/**
* Time between jobs is ITERATIONS X ITERATION_UNIT
* TODO:
* 
* 'ITERATION_UNIT' => 'H',
*	 "H"  # 'hour' 
*	 "i"  # 'minute' 
*	 "s"  # 'second'
*	 "n"  # 'month'
*	 "j"  # 'day' 
*	 "Y"  # 'year' 
*/

return array(
    'CLI_JOBS' => array(
		'CRON' => array(
			'CRON_NAME' => array(
				'JOB_NAME' => 'CRON_JOB_NAME',
				'JOB_DESCRIPTION' => 'fill it in, what does the job do',
				'SERVICE_CALL' => 'SERVICE\SERVICE_NAME\CLASS_NAME.method',
				'SERVICE_CALL_PARAMS' => NULL,
				'EXPIRE' => NULL,
				'ITERATIONS' => 3,
				'ITERATION_UNIT' => 'H',
			),
			
		),
    ),
);

?>