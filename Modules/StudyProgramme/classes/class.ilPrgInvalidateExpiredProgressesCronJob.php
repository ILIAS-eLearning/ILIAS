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
		$this->lng = $DIC['lng'];
		$this->lng->loadLanguageModule('prg');
	}

	const ID = 'prg_invalidate_expired_progresses';

	/**
	 * Get title
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		return $this->lng->txt('prg_invalidate_expired_progresses_title');
	}
	
	/**
	 * Get description
	 * 
	 * @return string
	 */
	public function getDescription()
	{
		return $this->lng->txt('prg_invalidate_expired_progresses_desc');
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
		return true;
	}
	
	/**
	 * Get schedule type
	 * 
	 * @return int
	 */
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_DAYS;
	}
	
	/**
	 * Get schedule value
	 * 
	 * @return int|array
	 */
	public function getDefaultScheduleValue()
	{
		return 1;
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
		$usr_id = $this->usr && $this->usr->getId() ? $this->usr->getId() : SYSTEM_USER_ID;
		foreach ($this->user_progress_db->getExpiredSuccessfulInstances() as $progress) {
			try {
				$progress->markFailed($usr_id);
			} catch (ilException $e) {
				$this->log->write('an error occured: '.$e->getMessage());
			}
		}
		return $result;
	}
}