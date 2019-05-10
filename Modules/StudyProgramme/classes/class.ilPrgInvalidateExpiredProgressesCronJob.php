<?php


class ilPrgInvalidateExpiredProgressesCronJob extends ilCronJob
{

	protected $user_progress_db;

	public function __construct()
	{
		global $DIC;

		$this->user_progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
		$this->usr = $DIC['ilUser'];
		$this->log = $DIC['ilLog'];
	}

	const ID = 'prg_invalidate_expired_progresses';

	/**
	 * Get title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		return 'Limited Study Programme validity';
	}
	
	/**
	 * Get description
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return 'Invalidate expired Study Programme progresses';
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
		foreach ($this->user_progress_db->getExpiredSuccessfulInstances() as $progress) {
			try {
				$progress->markFailed(6);
			} catch (ilException $e) {
				$this->log->write('an error occured: '.$e->getMessage());
			}
		}
		return $result;
	}
}