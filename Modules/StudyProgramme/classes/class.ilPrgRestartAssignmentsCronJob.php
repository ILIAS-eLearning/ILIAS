<?php

class ilPrgRestartAssignmentsCronJob extends ilCronJob
{
	protected $user_progress_db;

	public function __construct()
	{
		global $DIC;

		$this->user_assignments_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
		$this->usr = $DIC['ilUser'];
		$this->log = $DIC['ilLog'];
	}

	const ID = 'prg_restart_assignments_temporal_progress';

	/**
	 * Get title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		return 'Study Programme assignments restart';
	}
	
	/**
	 * Get description
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return 'Restarts assignments for failed programmes due to limited validity';
	}
	/**
	 * Get id
	 * 
	 * @return string
	 */
	public function getId()
	{
		return self::ID;
	}
	
	/**
	 * Is to be activated on "installation"
	 * 
	 * @return boolean
	 */
	public function hasAutoActivation()
	{
		return true;
	}

	/**
	 * Can the schedule be configured?
	 * 
	 * @return boolean
	 */
	public function hasFlexibleSchedule()
	{
		return false;
	}
	
	/**
	 * Get schedule type
	 * 
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_MINUTES;
	}
	
	/**
	 * Get schedule value
	 * 
	 * @return int|array
	 */
	public function getDefaultScheduleValue()
	{
		return 5;
	}
		
	/**
	 * Run job
	 * 
	 * @return ilCronJobResult
	 */
	public function run()
	{
		$result = new ilCronJobResult();
		$result->setStatus(ilCronJobResult::STATUS_OK);
		foreach ($this->user_assignments_db->getDueToRestartInstances() as $assignment) {
			try {
				$assignment->restartAssignment();
			} catch (ilException $e) {
				$this->log->write('an error occured: '.$e->getMessage());
			}
		}
		return $result;
	}
}