<?php


class ilStudyProgrammeProgressDBRepository
implements ilStudyProgrammeProgressRepository
{
	protected static $cache = [];
	protected $db;

	const TABLE = 'prg_usr_progress';

	const FIELD_ID = 'id';
	const FIELD_ASSIGNMENT_ID = 'assignment_id';
	const FIELD_PRG_ID = 'prg_id';
	const FIELD_USR_ID = 'usr_id';
	const FIELD_POINTS = 'points';
	const FIELD_POINTS_CUR = 'points_cur';
	const FIELD_STATUS = 'status';
	const FIELD_COMPLETION_BY = 'completion_by';
	const FIELD_ASSIGNMENT_DATE = 'assignment_date';
	const FIELD_LAST_CHANGE = 'last_change';
	const FIELD_LAST_CHANGE_BY = 'last_change_by';
	const FIELD_COMPLETION_DATE = 'completion_date';
	const FIELD_DEADLINE = 'deadline';
	const FIELD_VQ_DATE = 'vq_date';
	const FIELD_INVALIDATED = 'invalidated';

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createFor(
		ilStudyProgrammeSettings $prg,
		ilStudyProgrammeAssignment $ass
	) : ilStudyProgrammeProgress
	{
		$id = $this->nextId();
		$progress = new ilStudyProgrammeProgress($id);
		$row = [
			self::FIELD_ID => $id,
			self::FIELD_ASSIGNMENT_ID => $ass->getId(),
			self::FIELD_PRG_ID => $prg->getObjId(),
			self::FIELD_USR_ID => $ass->getUserId(),
			self::FIELD_POINTS => $prg->getPoints(),
			self::FIELD_POINTS_CUR => 0,
			self::FIELD_STATUS => ilStudyProgrammeProgress::STATUS_IN_PROGRESS,
			self::FIELD_COMPLETION_BY => null,
			self::FIELD_LAST_CHANGE => \ilUtil::now(),
			self::FIELD_ASSIGNMENT_DATE => \ilUtil::now(),
			self::FIELD_LAST_CHANGE_BY => null,
			self::FIELD_COMPLETION_DATE => null,
			self::FIELD_DEADLINE => null,
			self::FIELD_VQ_DATE => null,
			self::FIELD_INVALIDATED => 0
		];
		$this->insertRowDB($row);
		return $this->buildByRow($row);
	}

	/**
	 * @inheritdoc
	 */
	public function read(int $id) : ilStudyProgrammeProgress
	{
		foreach ($this->loadByFilter([self::FIELD_ID => $id]) as $row) {
			return $this->buildByRow($row);
		}
		throw new \ilException('invalid id '.$id);
	}


	/**
	 * @inheritdoc
	 */
	public function readByIds(
		int $prg_id,
		int $assignment_id,
		int $usr_id
	) : ilStudyProgrammeProgress
	{
		return $this->readByPrgIdAndAssignmentId($prg_id,$assignment_id);
	}

	/**
	 * @inheritdoc
	 */
	public function readByPrgIdAndAssignmentId(
		int $prg_id,
		int $assignment_id
	)
	{
		foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id,self::FIELD_ASSIGNMENT_ID => $assignment_id]) as $row) {
			return $this->buildByRow($row);
		}

	}

	/**
	 * @inheritdoc
	 */
	public function readByPrgIdAndUserId(int $prg_id, int $usr_id) : array
	{
		$return = [];
		foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id,self::FIELD_USR_ID => $usr_id]) as $row) {
			$return[] = $this->buildByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function readByPrgId(int $prg_id) : array
	{
		$return = [];
		foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id]) as $row) {
			$return[] = $this->buildByRow($row);
		}
		return $return;
	}

	public function readFirstByPrgId(int $prg_id)
	{
		$return = [];
		foreach ($this->loadByFilter([self::FIELD_PRG_ID => $prg_id]) as $row) {
			return $this->buildByRow($row);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function readByAssignmentId(int $assignment_id) : array
	{
		$return = [];
		foreach ($this->loadByFilter([self::FIELD_ASSIGNMENT_ID => $assignment_id]) as $row) {
			$return[] = $this->buildByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function readExpiredSuccessfull() : array
	{
		$return = [];
		foreach ($this->loadExpiredSuccessful() as $row) {
			$return[] = $this->buildByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function update(ilStudyProgrammeProgress $progress)
	{
		$this->updateRowDB(
			[
				self::FIELD_ID => $progress->getId(),
				self::FIELD_ASSIGNMENT_ID => $progress->getAssignmentId(),
				self::FIELD_PRG_ID => $progress->getNodeId(),
				self::FIELD_USR_ID => $progress->getUserId(),
				self::FIELD_STATUS => $progress->getStatus(),
				self::FIELD_POINTS => $progress->getAmountOfPoints(),
				self::FIELD_POINTS_CUR => $progress->getCurrentAmountOfPoints(),
				self::FIELD_COMPLETION_BY => $progress->getCompletionBy(),
				self::FIELD_LAST_CHANGE_BY => $progress->getLastChangeBy(),
				self::FIELD_LAST_CHANGE => $progress->getLastChange()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT),
				self::FIELD_ASSIGNMENT_DATE => $progress->getAssignmentDate()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT),
				self::FIELD_COMPLETION_DATE =>
					$progress->getCompletionDate() ?
					$progress->getCompletionDate()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT) : null,
				self::FIELD_DEADLINE => $progress->getDeadline() ? $progress->getDeadline()->format(ilStudyProgrammeProgress::DATE_FORMAT) : null,
				self::FIELD_VQ_DATE => $progress->getValidityOfQualification() ? $progress->getValidityOfQualification()->format(ilStudyProgrammeProgress::DATE_TIME_FORMAT) : null,
				self::FIELD_INVALIDATED => $progress->isInvalidated() ? 1 : 0
			]
		);
	}

	/**
	 * @inheritdoc
	 */	
	public function delete(ilStudyProgrammeProgress $progress)
	{
		$this->deleteDB($progress->getId());
	}

	protected function insertRowDB(array $row)
	{
		$this->db->insert(
			self::TABLE,
			[
				self::FIELD_ID => ['interger',$row[self::FIELD_ID]]
				,self::FIELD_ASSIGNMENT_ID => ['interger',$row[self::FIELD_ASSIGNMENT_ID]]
				,self::FIELD_PRG_ID => ['interger',$row[self::FIELD_PRG_ID]]
				,self::FIELD_USR_ID => ['interger',$row[self::FIELD_USR_ID]]
				,self::FIELD_STATUS => ['interger',$row[self::FIELD_STATUS]]
				,self::FIELD_POINTS => ['interger',$row[self::FIELD_POINTS]]
				,self::FIELD_POINTS_CUR => ['interger',$row[self::FIELD_POINTS_CUR]]
				,self::FIELD_COMPLETION_BY => ['interger',$row[self::FIELD_COMPLETION_BY]]
				,self::FIELD_LAST_CHANGE_BY => ['interger',$row[self::FIELD_LAST_CHANGE_BY]]
				,self::FIELD_LAST_CHANGE => ['text',$row[self::FIELD_LAST_CHANGE]]
				,self::FIELD_ASSIGNMENT_DATE => ['timestamp',$row[self::FIELD_ASSIGNMENT_DATE]]
				,self::FIELD_COMPLETION_DATE => ['timestamp',$row[self::FIELD_COMPLETION_DATE]]
				,self::FIELD_DEADLINE => ['text',$row[self::FIELD_DEADLINE]]
				,self::FIELD_VQ_DATE => ['timestamp',$row[self::FIELD_VQ_DATE]]
				,self::FIELD_INVALIDATED => ['timestamp',$row[self::FIELD_INVALIDATED]]
			]
		);
	}

	public function deleteDB(int $id)
	{
		$this->db->manipulate(
			'DELETE FROM '.self::TABLE.' WHERE '.self::FIELD_ID.' = '.$this->db->quote($id,'integer')
		);
	}

	protected function updateRowDB(array $values)
	{
		$q = 'UPDATE '.self::TABLE
			.'	SET'
			.'	'.self::FIELD_ASSIGNMENT_ID.' = '.$this->db->quote($values[self::FIELD_ASSIGNMENT_ID],'integer')
			.'	,'.self::FIELD_PRG_ID.' = '.$this->db->quote($values[self::FIELD_PRG_ID],'integer')
			.'	,'.self::FIELD_USR_ID.' = '.$this->db->quote($values[self::FIELD_USR_ID],'integer')
			.'	,'.self::FIELD_STATUS.' = '.$this->db->quote($values[self::FIELD_STATUS],'integer')
			.'	,'.self::FIELD_POINTS.' = '.$this->db->quote($values[self::FIELD_POINTS],'integer')
			.'	,'.self::FIELD_POINTS_CUR.' = '.$this->db->quote($values[self::FIELD_POINTS_CUR],'integer')
			.'	,'.self::FIELD_COMPLETION_BY.' = '.$this->db->quote($values[self::FIELD_COMPLETION_BY],'integer')
			.'	,'.self::FIELD_LAST_CHANGE_BY.' = '.$this->db->quote($values[self::FIELD_LAST_CHANGE_BY],'integer')
			.'	,'.self::FIELD_LAST_CHANGE.' = '.$this->db->quote($values[self::FIELD_LAST_CHANGE],'text')
			.'	,'.self::FIELD_ASSIGNMENT_DATE.' = '.$this->db->quote($values[self::FIELD_ASSIGNMENT_DATE],'timestamp')
			.'	,'.self::FIELD_COMPLETION_DATE.' = '.$this->db->quote($values[self::FIELD_COMPLETION_DATE],'timestamp')
			.'	,'.self::FIELD_DEADLINE.' = '.$this->db->quote($values[self::FIELD_DEADLINE],'text')
			.'	,'.self::FIELD_VQ_DATE.' = '.$this->db->quote($values[self::FIELD_VQ_DATE],'timestamp')
			.'	,'.self::FIELD_INVALIDATED.' = '.$this->db->quote($values[self::FIELD_INVALIDATED],'integer')
			.'	WHERE '.self::FIELD_ID.' = '.$this->db->quote($values[self::FIELD_ID],'integer')
		;
		$this->db->manipulate($q);
	}

	protected function buildByRow(array $row) : ilStudyProgrammeProgress
	{
		$prgrs = (new ilStudyProgrammeProgress($row[self::FIELD_ID]))
			->setAssignmentId($row[self::FIELD_ASSIGNMENT_ID])
			->setNodeId($row[self::FIELD_PRG_ID])
			->setUserId($row[self::FIELD_USR_ID])
			->setStatus($row[self::FIELD_STATUS])
			->setAmountOfPoints($row[self::FIELD_POINTS])
			->setCurrentAmountOfPoints($row[self::FIELD_POINTS_CUR])
			->setCompletionBy($row[self::FIELD_COMPLETION_BY])
			->setDeadline(
				$row[self::FIELD_DEADLINE] ?
				DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_FORMAT,$row[self::FIELD_DEADLINE]) :
				null
			)
			->setAssignmentDate(
				DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT,$row[self::FIELD_ASSIGNMENT_DATE])
			)
			->setCompletionDate(
				$row[self::FIELD_COMPLETION_DATE] ?
				DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT,$row[self::FIELD_COMPLETION_DATE]) :
				null
			)
			->setLastChange(
				$row[self::FIELD_LAST_CHANGE] ?
				DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT,$row[self::FIELD_LAST_CHANGE]) :
				null
			)
			->setValidityOfQualification(
				$row[self::FIELD_VQ_DATE] ?
				DateTime::createFromFormat(ilStudyProgrammeProgress::DATE_TIME_FORMAT,$row[self::FIELD_VQ_DATE]) :
				null
			);
		if((int)$row[self::FIELD_INVALIDATED] === 1) {
			$prgrs = $prgrs->invalidate();
		}
		return $prgrs;

	}

	protected function loadByFilter(array $filter) 
	{
		$q = 'SELECT '.self::FIELD_ID
			.'	,'.self::FIELD_ASSIGNMENT_ID
			.'	,'.self::FIELD_PRG_ID
			.'	,'.self::FIELD_USR_ID
			.'	,'.self::FIELD_STATUS
			.'	,'.self::FIELD_POINTS
			.'	,'.self::FIELD_POINTS_CUR
			.'	,'.self::FIELD_COMPLETION_BY
			.'	,'.self::FIELD_LAST_CHANGE
			.'	,'.self::FIELD_LAST_CHANGE_BY
			.'	,'.self::FIELD_ASSIGNMENT_DATE
			.'	,'.self::FIELD_COMPLETION_DATE
			.'	,'.self::FIELD_DEADLINE
			.'	,'.self::FIELD_VQ_DATE
			.'	,'.self::FIELD_INVALIDATED
			.'	FROM '.self::TABLE
			.'	WHERE TRUE';
		foreach ($filter as $field => $value) {
			$q .= '	AND '.$field.' = '.$this->db->quote($value,'text');
		}
		$res = $this->db->query($q);
		while($rec = $this->db->fetchAssoc($res)) {
			yield $rec;
		}
	}

	protected function loadExpiredSuccessful()
	{
		$q = 'SELECT '.self::FIELD_ID
			.'	,'.self::FIELD_ASSIGNMENT_ID
			.'	,'.self::FIELD_PRG_ID
			.'	,'.self::FIELD_USR_ID
			.'	,'.self::FIELD_STATUS
			.'	,'.self::FIELD_POINTS
			.'	,'.self::FIELD_POINTS_CUR
			.'	,'.self::FIELD_COMPLETION_BY
			.'	,'.self::FIELD_LAST_CHANGE
			.'	,'.self::FIELD_LAST_CHANGE_BY
			.'	,'.self::FIELD_ASSIGNMENT_DATE
			.'	,'.self::FIELD_COMPLETION_DATE
			.'	,'.self::FIELD_DEADLINE
			.'	,'.self::FIELD_VQ_DATE
			.'	,'.self::FIELD_INVALIDATED
			.'	FROM '.self::TABLE
			.'	WHERE '.$this->db->in(
							self::FIELD_STATUS,
							[
								ilStudyProgrammeProgress::STATUS_ACCREDITED,
								ilStudyProgrammeProgress::STATUS_COMPLETED
							],
							false,
							'integer'
						)
			.'		AND '.self::FIELD_VQ_DATE.' IS NOT NULL'
			.'		AND DATE('.self::FIELD_VQ_DATE.') < '
							.$this->db->quote(
								(new DateTime())->format(ilStudyProgrammeProgress::DATE_FORMAT)
								,'text'
							)
			.'		AND '.self::FIELD_INVALIDATED.' != 1 OR '.self::FIELD_INVALIDATED.' IS NULL';

		$res = $this->db->query($q);
		while($rec = $this->db->fetchAssoc($res)) {
			yield $rec;
		}
	}

	protected function nextId() : int
	{
		return (int)$this->db->nextId(self::TABLE);
	}
}
