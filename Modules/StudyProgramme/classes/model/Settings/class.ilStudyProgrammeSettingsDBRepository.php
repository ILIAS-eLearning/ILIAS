<?php


class ilStudyProgrammeSettingsDBRepository
implements ilStudyProgrammeSettingsRepository
{
	protected static $cache = [];
	protected $db;

	const TABLE = 'prg_settings';

	const FIELD_OBJ_ID = 'obj_id';
	const FIELD_SUBTYPE_ID = 'subtype_id';
	const FIELD_STATUS = 'status';
	const FIELD_LP_MODE = 'lp_mode';
	const FIELD_POINTS = 'points';
	const FIELD_LAST_CHANGED = 'last_change';


	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createFor(int $obj_id) : ilStudyProgrammeSettings
	{
		$prg = new ilStudyProgrammeSettings($obj_id);
		$this->insertDB(
			$obj_id,
			ilStudyProgrammeSettings::DEFAULT_SUBTYPE,
			ilStudyProgrammeSettings::STATUS_DRAFT,
			ilStudyProgrammeSettings::MODE_UNDEFINED,
			ilStudyProgrammeSettings::DEFAULT_POINTS,
			(new DateTime())->format('Y-m-s H:i:s')
		);
		$prg->setSubtypeId(ilStudyProgrammeSettings::DEFAULT_SUBTYPE)
			->setStatus(ilStudyProgrammeSettings::STATUS_DRAFT)
			->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)
			->setPoints(ilStudyProgrammeSettings::DEFAULT_POINTS);
		self::$cache[$obj_id] = $prg;
		return $prg;
	}

	/**
	 * @inheritdoc
	 */
	public function read(int $obj_id) : ilStudyProgrammeSettings
	{
		if(!array_key_exists($obj_id, self::$cache)) {
			self::$cache[$obj_id] = $this->loadDB($obj_id);
		}
		return self::$cache[$obj_id];
	}

	/**
	 * @inheritdoc
	 */
	public function update(ilStudyProgrammeSettings $settings)
	{
		$this->updateDB(
			$settings->getObjId(),
			$settings->getSubtypeId(),
			$settings->getStatus(),
			$settings->getLPMode(),
			$settings->getPoints(),
			$settings->getLastChange()->get(IL_CAL_DATETIME)
		);
		$this->cache[$settings->getObjId()] = $settings;
	}

	/**
	 * @inheritdoc
	 */	
	public function delete(ilStudyProgrammeSettings $settings)
	{
		unset(self::$cache[$settings->getObjId()]);
		$this->deleteDB($settings->getObjId());
	}

	/**
	 * @inheritdoc
	 */
	public function loadByType(int $type_id) : array
	{
		$q = 'SELECT '.self::FIELD_SUBTYPE_ID
			.'	,'.self::FIELD_STATUS
			.'	,'.self::FIELD_POINTS
			.'	,'.self::FIELD_LP_MODE
			.'	,'.self::FIELD_LAST_CHANGED
			.'	,'.self::FIELD_OBJ_ID
			.'	FROM '.self::TABLE
			.'	WHERE '.self::FIELD_SUBTYPE_ID.' = '.$this->db->quote($type_id,'integer');
		$res = $this->db->query($q);
		$return = [];
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = $this->createByRow($rec);
		}
		return $return;
	}
	

	public function loadIdsByType(int $type_id) : array
	{
		return [];
	}
	protected function insertDB(
		int $obj_id,
		int $subtype_id,
		int $status,
		int $lp_mode,
		int $points,
		string $last_change
	)
	{
		$this->db->insert(
			self::TABLE,
			[
				self::FIELD_OBJ_ID => ['integer',$obj_id],
				self::FIELD_SUBTYPE_ID => ['integer',$subtype_id],
				self::FIELD_STATUS => ['integer',$status],
				self::FIELD_POINTS => ['integer',$points],
				self::FIELD_LP_MODE => ['integer',$lp_mode],
				self::FIELD_LAST_CHANGED => ['timestamp',$last_change]
			]
		);
	}

	protected function loadDB(int $obj_id) : ilStudyProgrammeSettings
	{
		$rec = $this->db->fetchAssoc(
			$this->db->query(
				'SELECT '.self::FIELD_SUBTYPE_ID
				.'	,'.self::FIELD_STATUS
				.'	,'.self::FIELD_POINTS
				.'	,'.self::FIELD_LP_MODE
				.'	,'.self::FIELD_LAST_CHANGED
				.'	,'.self::FIELD_OBJ_ID
				.'	FROM '.self::TABLE
				.'	WHERE '.self::FIELD_OBJ_ID.' = '.$this->db->quote($obj_id,'integer')
			)
		);
		if(!$rec) {
			throw new \LogicException('invaid obj_id to load: '.$obj_id);
		}
		return $this->createByRow($rec);
	}

	protected function createByRow(array $row) : ilStudyProgrammeSettings
	{
		return (new ilStudyProgrammeSettings($row[self::FIELD_OBJ_ID]))
			->setSubtypeId($row[self::FIELD_SUBTYPE_ID])
			->setStatus($row[self::FIELD_STATUS])
			->setLPMode($row[self::FIELD_LP_MODE])
			->setPoints($row[self::FIELD_POINTS])
			->setLastChange(new \ilDateTime($row[self::FIELD_LAST_CHANGED],IL_CAL_DATETIME));
	}

	protected function deleteDB(int $obj_id)
	{
		if(!$this->checkExists($obj_id)) {
			throw new \LogicException('invaid obj_id to delete: '.$obj_id);
		}
		$this->db->manipulate(
			'DELETE FROM '.self::TABLE
			.'	WHERE '.self::FIELD_OBJ_ID.' = '.$this->db->quote($obj_id,'integer')
		);
	}

	protected function updateDB(
		int $obj_id,
		int $subtype_id,
		int $status,
		int $lp_mode,
		int $points,
		string $last_change
	)
	{
		if(!$this->checkExists($obj_id)) {
			throw new \LogicException('invaid obj_id to update: '.$obj_id);
		}
		$this->db->manipulate(
			'UPDATE '.self::TABLE.' SET'
			.'	'.self::FIELD_SUBTYPE_ID.' = '.$this->db->quote($subtype_id,'integer')
			.'	,'.self::FIELD_STATUS.' = '.$this->db->quote($status,'integer')
			.'	,'.self::FIELD_LP_MODE.' = '.$this->db->quote($lp_mode,'integer')
			.'	,'.self::FIELD_POINTS.' = '.$this->db->quote($points,'integer')
			.'	,'.self::FIELD_LAST_CHANGED.' = '.$this->db->quote($last_change,'timestamp')
			.'	WHERE '.self::FIELD_OBJ_ID.' = '.$this->db->quote($obj_id,'integer')
		);
	}

	protected function checkExists(int $obj_id)
	{
		$rec = $this->db->fetchAssoc(
			$this->db->query(
				'SELECT '.self::FIELD_OBJ_ID
				.'	FROM '.self::TABLE
				.'	WHERE '.self::FIELD_OBJ_ID.' = '.$this->db->quote($obj_id,'integer')
			)
		);
		if($rec) {
			return true;
		}
		return false;
	}
}
