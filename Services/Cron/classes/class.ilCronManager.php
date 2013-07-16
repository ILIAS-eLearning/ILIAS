<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron management
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronManager
{		
	/**
	 * Run all active jobs
	 */
	public static function runActiveJobs()
	{
		global $ilLog, $ilSetting;
		
		// separate log for cron
		// $this->log->setFilename($_COOKIE["ilClientId"]."_cron.txt");
		
		$ilLog->write("CRON - batch start");
		
		$ilSetting->set('last_cronjob_start_ts', time());
		
		foreach(self::getCronJobData(null, false) as $row)
		{					
			self::runJob($row);
		}		
		
		$ilLog->write("CRON - batch end");
	}
	
	/**
	 * Run single job manually
	 * 
	 * @param string $a_job_id
	 * @return bool
	 */
	public static function runJobManual($a_job_id)
	{
		global $ilLog;
		
		$result = false;
		
		$ilLog->write("CRON - manual start (".$a_job_id.")");
		
		$job_data = array_pop(self::getCronJobData($a_job_id, false));
		if($job_data["job_id"] == $a_job_id)
		{
			$result = self::runJob($job_data, true);						
		}
		else
		{
			$ilLog->write("CRON - job ".$a_job_id." seems invalid or is inactive");
		}
		
		$ilLog->write("CRON - manual end (".$a_job_id.")");
		
		return $result;
	}
		
	/**
	 * Run single cron job (internal)
	 * 
	 * @param array $a_job_data
	 * @param bool $a_manual
	 * @return boolean
	 */
	protected static function runJob(array $a_job_data, $a_manual = false)
	{
		global $ilLog, $ilDB;
		
		$did_run = false;
	
		$job = self::getJobInstance($a_job_data["job_id"], $a_job_data["component"], 
			$a_job_data["class"], $a_job_data["path"]);
		if($job)
		{			
			include_once "Services/Cron/classes/class.ilCronJobResult.php";		
			
			// already running?
			if($a_job_data["alive_ts"])
			{
				$ilLog->write("CRON - job ".$a_job_data["job_id"]." still running");
				
				$cut = 60*60*3; // 3h				
				
				// is running (and has not pinged) for 3 hours straight, we assume it crashed
				if(time()-$a_job_data["alive_ts"] > $cut)
				{
					$ilDB->manipulate("UPDATE cron_job SET".
						" running_ts = ".$ilDB->quote(0, "integer").
						" , alive_ts = ".$ilDB->quote(0, "integer").							
						" WHERE job_id = ".$ilDB->quote($a_job_data["job_id"], "text"));

					self::deactivateJob($job);

					$result = new ilCronJobResult();
					$result->setStatus(ilCronJobResult::STATUS_CRASHED);
					$result->setCode("job_auto_deactivation_time_limit");
					$result->setMessage("Cron job deactivated because it has been inactive for 3 hours");

					if(!$a_manual)
					{
						self::sendNotification($job, $result);
					}

					self::updateJobResult($job, $result, $a_manual);							

					$ilLog->write("CRON - job ".$a_job_data["job_id"]." deactivated (assumed crash)");
				}		
			}
			// initiate run?
			else if($job->isActive($a_job_data["job_result_ts"], 
				$a_job_data["schedule_type"], $a_job_data["schedule_value"], $a_manual))
			{
				$ilLog->write("CRON - job ".$a_job_data["job_id"]." started");

				$ilDB->manipulate("UPDATE cron_job SET".
					" running_ts = ".$ilDB->quote(time(), "integer").
					" , alive_ts = ".$ilDB->quote(time(), "integer").
					" WHERE job_id = ".$ilDB->quote($a_job_data["job_id"], "text"));

				$ts_in = self::getMicrotime();					
				$result = $job->run();
				$ts_dur = self::getMicrotime()-$ts_in;

				// no proper result 
				if(!$result instanceof ilCronJobResult)
				{
					$result = new ilCronJobResult();
					$result->setStatus(ilCronJobResult::STATUS_CRASHED);
					$result->setCode("job_no_result");
					$result->setMessage("Cron job did not return a proper result");

					if(!$a_manual)
					{
						self::sendNotification($job, $result);
					}

					$ilLog->write("CRON - job ".$a_job_data["job_id"]." no result");
				}
				// no valid configuration, job won't work
				else if($result->getStatus() == ilCronJobResult::STATUS_INVALID_CONFIGURATION)
				{
					self::deactivateJob($job);

					if(!$a_manual)
					{
						self::sendNotification($job, $result);	
					}

					$ilLog->write("CRON - job ".$a_job_data["job_id"]." invalid configuration");
				}
				// success!
				else
				{
					$did_run = true;
				}

				$result->setDuration($ts_dur);

				self::updateJobResult($job, $result, $a_manual);

				$ilDB->manipulate("UPDATE cron_job SET".
					" running_ts = ".$ilDB->quote(0, "integer").
					" , alive_ts = ".$ilDB->quote(0, "integer").
					" WHERE job_id = ".$ilDB->quote($a_job_data["job_id"], "text"));		

				$ilLog->write("CRON - job ".$a_job_data["job_id"]." finished");
			}
			else
			{
				$ilLog->write("CRON - job ".$a_job_data["job_id"]." returned status inactive");
			}
		}		
		
		return $did_run;
	}
	
	/**
	 * Get job instance (by job id)
	 * 
	 * @param string $a_job_id
	 * @return ilCronJob
	 */
	public static function getJobInstanceById($a_job_id)
	{
		global $ilLog;
		
		$job_data = array_pop(self::getCronJobData($a_job_id));
		if($job_data["job_id"] == $a_job_id)
		{
			return self::getJobInstance($job_data["job_id"], $job_data["component"], 
				$job_data["class"], $job_data["path"]);				
		}
		else
		{
			$ilLog->write("CRON - job ".$a_job_id." seems invalid or is inactive");
		}
	}
	
	/**
	 * Get job instance (by job data)
	 * 
	 * @param string $a_component
	 * @param string $a_class
	 * @param string $a_path
	 * @return ilCronJob
	 */
	public static function getJobInstance($a_id, $a_component, $a_class, $a_path = null)
	{
		global $ilLog;
		
		if(!$a_path)
		{
			$a_path = $a_component."/classes/";
		}		
		$class_file = $a_path."class.".$a_class.".php";							
		if(file_exists($class_file))
		{										
			include_once $class_file;
			if(class_exists($a_class))
			{				
				$job = new $a_class();				
				if($job instanceof ilCronJob)
				{
					if($job->getId() == $a_id)
					{
						return $job;
					}
				}
			}
		}
		
		$ilLog->write("Cron XML - Job ".$a_id." in class ".$a_class." (".
			$class_file.") is invalid.");
	}
	
	/**
	 * Send notification to admin about job event(s)
	 * 
	 * @param ilCronJob $a_job
	 * @param string $a_message
	 */
	protected static function sendNotification(ilCronJob $a_job, $a_message)
	{
		// :TODO:
	}	
	
	/**
	 * Process data from module.xml/service.xml
	 *
	 * @param string $a_component
	 * @param string $a_id
	 * @param string $a_class
	 * @param string $_path
	 */
	public static function updateFromXML($a_component, $a_id, $a_class, $a_path = null)
	{
		global $ilDB, $ilLog;
		
		if(!$ilDB->tableExists("cron_job"))
		{
			return;
		}
		
		// only if job seems valid
		$job = self::getJobInstance($a_id, $a_component, $a_class, $a_path);
		if($job)
		{	
			// already exists?			
			$sql = "SELECT job_id, schedule_type FROM cron_job".
				" WHERE component = ".$ilDB->quote($a_component, "text").
				" AND job_id = ".$ilDB->quote($a_id, "text");
			$set = $ilDB->query($sql);
			$row = $ilDB->fetchAssoc($set);
			$job_exists = ($row["job_id"] == $a_id);
			$schedule_type = $row["schedule_type"];
		
			// new job
			if(!$job_exists)
			{							
				$sql = "INSERT INTO cron_job (job_id, component, class, path)".
					" VALUES (".$ilDB->quote($job->getId(), "text").", ".
					$ilDB->quote($a_component, "text").", ".
					$ilDB->quote($a_class, "text").", ".
					$ilDB->quote($a_path, "text").")";
				$ilDB->manipulate($sql);

				$ilLog->write("Cron XML - Job ".$job->getId()." in class ".$a_class.
					" added.");

				// only if flexible
				self::updateJobSchedule($job,  
					$job->getDefaultScheduleType(),
					$job->getDefaultScheduleValue());

				if($job->hasAutoActivation())
				{
					self::activateJob($job);							
				}														
			}	
			// existing job - but schedule is flexible now
			else if($job->hasFlexibleSchedule() && !$schedule_type)
			{
				self::updateJobSchedule($job,  
					$job->getDefaultScheduleType(),
					$job->getDefaultScheduleValue());
			}
			// existing job - but schedule is static now
			else if(!$job->hasFlexibleSchedule() && $schedule_type)
			{
				self::updateJobSchedule($job, null, null);
			}
		}												
	}
	
	/**
	 * Clear job data
	 * 
	 * @param string $a_component
	 * @param array $a_xml_job_ids
	 */
	public static function clearFromXML($a_component, array $a_xml_job_ids)
	{
		global $ilDB, $ilLog;	
		
		if(!$ilDB->tableExists("cron_job"))
		{
			return;
		}
		
		// gather existing jobs
		$all_jobs = array();
		$sql = "SELECT job_id FROM cron_job".
			" WHERE component = ".$ilDB->quote($a_component, "text");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$all_jobs[] = $row["job_id"];
		}
		
		if(sizeof($all_jobs))
		{
			if(sizeof($a_xml_job_ids))
			{
				// delete obsolete job data
				foreach($all_jobs as $job_id)
				{
					if(!in_array($job_id, $a_xml_job_ids))
					{				
						$ilDB->manipulate("DELETE FROM cron_job".
							" WHERE component = ".$ilDB->quote($a_component, "text").
							" AND job_id = ".$ilDB->quote($job_id, "text"));	

						$ilLog->write("Cron XML - Job ".$job_id." in class ".$a_component.
								" deleted.");
					}
				}		
			}
			else
			{		
				$ilDB->manipulate("DELETE FROM cron_job".
					" WHERE component = ".$ilDB->quote($a_component, "text"));			

				$ilLog->write("Cron XML - All jobs deleted for ".$a_component." as component is inactive.");
			}
		}
	}
	
	/**
	 * Get cron job configuration/execution data
	 * 
	 * @param array|string $a_id
	 * @param array $a_include_inactive
	 * @return array
	 */
	public static function getCronJobData($a_id = null, $a_include_inactive = true)
	{
		global $ilDB;
		
		$res = array();
		
		if($a_id && !is_array($a_id))
		{
			$a_id = array($a_id);
		}	
		
		$sql = "SELECT * FROM cron_job";
		
		$where = array();
		if($a_id)
		{
			$where[] = $ilDB->in("job_id", $a_id, "", "text");
		}
		if(!$a_include_inactive)
		{
			$where[] = "job_status = ".$ilDB->quote(1, "integer");
		}
		if(sizeof($where))
		{
			$sql .= " WHERE ".implode(" AND ", $where);			
		}
		
		// :TODO: discuss job execution order
		$sql .= " ORDER BY job_id";
		
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		
		return $res;
	}
	
	/**
	 * Reset job
	 * 
	 * @param ilCronJob $a_job
	 */
	public static function resetJob(ilCronJob $a_job)
	{
		global $ilDB;
		
		include_once "Services/Cron/classes/class.ilCronJobResult.php";
		$result = new ilCronJobResult();
		$result->setStatus(ilCronJobResult::STATUS_RESET);
		$result->setCode("job_manual_reset");
		$result->setMessage("Cron job re-activated by admin");		
		self::updateJobResult($a_job, $result, true);
				
		$ilDB->manipulate("UPDATE cron_job".
			" SET running_ts = ".$ilDB->quote(0, "integer").
			" , alive_ts = ".$ilDB->quote(0, "integer").
			" , job_result_ts = ".$ilDB->quote(0, "integer").
			" WHERE job_id = ".$ilDB->quote($a_job->getId(), "text"));		
		
		self::activateJob($a_job, true);
	}
	
	/**
	 * Activate cron job
	 * 
	 * @param ilCronJob $a_job
	 * @param bool $a_manual
	 */
	public static function activateJob(ilCronJob $a_job, $a_manual = false)
	{
		global $ilDB, $ilUser;
		
		$user_id = $a_manual ? $ilUser->getId() : 0;
		
		$sql = "UPDATE cron_job SET ".
			" job_status = ".$ilDB->quote(1, "integer").
			" , job_status_user_id = ".$ilDB->quote($user_id, "integer").
			" , job_status_type = ".$ilDB->quote($a_manual, "integer").
			" , job_status_ts = ".$ilDB->quote(time(), "integer"). 
			" WHERE job_id = ".$ilDB->quote($a_job->getId(), "text");		
		$ilDB->manipulate($sql);
		
		$job->activationWasToggled(true);		
	}
	
	/**
	 * Deactivate cron job
	 * 
	 * @param ilCronJob $a_job
	 * @param bool $a_manual
	 */
	public static function deactivateJob(ilCronJob $a_job, $a_manual = false)
	{
		global $ilDB, $ilUser;
		
		$user_id = $a_manual ? $ilUser->getId() : 0;
		
		$sql = "UPDATE cron_job SET ".
			" job_status = ".$ilDB->quote(0, "integer").
			" , job_status_user_id = ".$ilDB->quote($user_id, "integer").
			" , job_status_type = ".$ilDB->quote($a_manual, "integer").
			" , job_status_ts = ".$ilDB->quote(time(), "integer"). 
			" WHERE job_id = ".$ilDB->quote($a_job->getId(), "text");
		$ilDB->manipulate($sql);
				
		$job->activationWasToggled(false);				
	}
	
	/**
	 * Save job result
	 * 
	 * @param ilCronJob $a_job
	 * @param ilCronJobResult $a_result
	 * @param bool $a_manual
	 */
	protected static function updateJobResult(ilCronJob $a_job, ilCronJobResult $a_result, $a_manual = false)
	{
		global $ilDB, $ilUser;
		
		$user_id = $a_manual ? $ilUser->getId() : 0;
		
		$sql = "UPDATE cron_job SET ".
			" job_result_status = ".$ilDB->quote($a_result->getStatus(), "integer").
			" , job_result_user_id = ".$ilDB->quote($user_id, "integer").
			" , job_result_code = ".$ilDB->quote($a_result->getCode(), "text").
			" , job_result_message = ".$ilDB->quote($a_result->getMessage(), "text"). 
			" , job_result_type = ".$ilDB->quote($a_manual, "integer"). 
			" , job_result_ts = ".$ilDB->quote(time(), "integer"). 
			" , job_result_dur = ".$ilDB->quote($a_result->getDuration()*1000, "integer").
			" WHERE job_id = ".$ilDB->quote($a_job->getId(), "text");		
		$ilDB->manipulate($sql);
	}
	
	/**
	 * Update job schedule
	 * 
	 * @param ilCronJob $a_job
	 * @param int $a_schedule_type
	 * @param int $a_schedule_value
	 */
	public static function updateJobSchedule(ilCronJob $a_job, $a_schedule_type, $a_schedule_value)
	{
		global $ilDB;
		
		if($a_schedule_type === null ||
			($a_job->hasFlexibleSchedule() && 
				in_array($a_schedule_type, $a_job->getValidScheduleTypes())))
		{
			$sql = "UPDATE cron_job SET ".
				" schedule_type = ".$ilDB->quote($a_schedule_type, "integer").
				" , schedule_value = ".$ilDB->quote($a_schedule_value, "integer").
				" WHERE job_id = ".$ilDB->quote($a_job->getId(), "text");
			$ilDB->manipulate($sql);
		}
	}
	
	/**
	 * Get current microtime
	 * 
	 * @return float
	 */
	protected static function getMicrotime()
	{	
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * Keep cron job alive
	 * 
	 * @param string $a_job_id
	 */
	public static function ping($a_job_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE cron_job SET ".
			" alive_ts = ".$ilDB->quote(time(), "integer").
			" WHERE job_id = ".$ilDB->quote($a_job_id, "text"));
	}
}

?>