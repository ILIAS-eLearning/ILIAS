<?php
chdir(dirname(__FILE__));
chdir('..');

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE["ilClientId"] = $_SERVER['argv'][3];
$_POST['username'] = $_SERVER['argv'][1];
$_POST['password'] = $_SERVER['argv'][2];

if($_SERVER['argc'] < 4)
{
	die("Usage: cron.php username password client\n");
}

try {
	include_once './include/inc.header.php';
} catch(ilLogException $exept) {
	echo "NOT OK - can not open log file";
	exit(2);
}

// Different Checks on cronjobs
function is_running($job_data) {
	return $job_data["running_ts"] !== "0";
};

function last_pinged($offset, $job_data) {
	return (time() - $job_data["alive_ts"]) < $offset;
};

function is_activated($job_data) {
	return $job_data["job_status"] == 1;
};

function has_max_running_time($time, $job_data) {
	return (time() - $job_data["running_ts"]) < $time;
}

function last_run($time, $job_data) {
	return (time() - $job_data["job_result_ts"]) < $time;
}

$watch_jobs = array
	( "gev_deadline_mailing" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_deferred_mailing" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_clean_waiting_list" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_exited_user_cleanup" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_express_user_cleanup" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_user_not_in_org_unit" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)
	, "gev_update_dbv" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		)
	/*, "gev_orgu_superior_mailing" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(3000, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running for more than 3000s."
		)*/
	, "dct_creation" => array
		( "check" => function($job_data) {
				return	(	(is_running($job_data) && last_pinged(180, $job_data))
						||	((!is_running($job_data)) && is_activated($job_data) && last_run(300, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running and did not ping for 180s or did not run for 5m."
		)
		, "gev_decentral_trainings_cleanup" => array
		( "check" => function($job_data) {
				return	is_activated($job_data)
					&&	(  !is_running($job_data) 
						|| (is_running($job_data) && has_max_running_time(600, $job_data))
						);
			}
		, "fail_message" => "Job is not active or running and did not ping for 180s or did not run for 5m."
		)
	);

include_once './Services/Cron/classes/class.ilCronManager.php';

$not_ok_jobs = array();
$output = "";

foreach($watch_jobs as $job_id => $params) {
	$output .= $job_id." - ";
	
	$job_data = ilCronManager::getCronJobData($job_id);
	if ($params["check"]($job_data[0])) {
		$output .= "OK";
	}
	else {
		$not_ok_jobs[] = $job_id;
		if ($params["fail_message"]) {
			$output .= $params["fail_message"];
		}
		else {
			$output .= "NOT OK";
		}
	}
	$output .= "\n";
}

if (count($not_ok_jobs) > 0) {
	echo "ILIAS CRON NOT OK - Broken jobs: ".implode(", ", $not_ok_jobs)."\n\n";
	echo $output;
	exit(1);
}
else {
	echo "ILIAS CRON OK - All cron jobs are fine.\n\n";
	echo $output;
	exit(0);
}

?>