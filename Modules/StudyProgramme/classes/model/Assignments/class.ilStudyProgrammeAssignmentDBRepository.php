<?php


class ilStudyProgrammeAssignmentDBRepository
implements ilStudyProgrammeAssignmentRepository
{

	protected $db;

	const TABLE = 'prg_usr_assignments';

	const FIELD_ID = 'id';
	const FIELD_USR_ID = 'usr_id';
	const FIELD_ROOT_PRG_ID = 'root_prg_id';
	const FIELD_LAST_CHANGE = 'last_change';
	const FIELD_LAST_CHANGE_BY = 'last_change_by';
	const FIELD_RESTART_DATE = 'restart_date';
	const FIELD_RESTARTED_ASSIGNMENT_ID = 'restarted_assignment_id';

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createFor(int $root_prg_id, int $usr_id, int $assigning_usr_id) : ilStudyProgrammeAssignment
	{
		if (ilObject::_lookupType($usr_id) != "usr") {
			throw new ilException("ilStudyProgrammeAssignment::createFor: '$usr_id' "
								 ."is no id of a user.");
		}
		if (ilObject::_lookupType($root_prg_id) != "prg") {
			throw new ilException("ilStudyProgrammeAssignment::createFor: '$root_prg_id' "
								 ."is no id of a prg.");
		}
		$row = [
			self::FIELD_ID => $this->nextId(),
			self::FIELD_USR_ID => $usr_id,
			self::FIELD_ROOT_PRG_ID => $root_prg_id,
			self::FIELD_LAST_CHANGE_BY => $assigning_usr_id,
			self::FIELD_LAST_CHANGE => ilUtil::now(),
			self::FIELD_RESTART_DATE => null,
			self::FIELD_RESTARTED_ASSIGNMENT_ID => ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT
		];
		$this->insertRowDB($row);
		return $this->assignmentByRow($row);
	}

	/**
	 * @inheritdoc
	 */
	public function read(int $id) : ilStudyProgrammeAssignment
	{
		foreach($this->loadByFilterDB([self::FIELD_ID => $id]) as $row) {
			return $this->assignmentByRow($row);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function readByUsrId(int $usr_id) : array
	{
		$return = [];
		foreach($this->loadByFilterDB([self::FIELD_USR_ID => $usr_id]) as $row) {
			$return[] = $this->assignmentByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function readByPrgId(int $prg_id) : array
	{
		$return = [];
		foreach($this->loadByFilterDB([self::FIELD_ROOT_PRG_ID => $prg_id]) as $row) {
			$return[] = $this->assignmentByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function readByUsrIdAndPrgId(int $usr_id, int $prg_id)
	{
		$return = [];
		foreach($this->loadByFilterDB(
			[self::FIELD_USR_ID => $usr_id
			,self::FIELD_ROOT_PRG_ID => $prg_id]) as $row) {
			$return[] = $this->assignmentByRow($row);
		}
		return $return;
	}

	/**
	 * @inheritdoc
	 */
	public function readDueToRestart() : array
	{
		$return = [];
		foreach ($this->loadDueToRestart() as $row) {
			$return[] = $this->assignmentByRow($row);
		}
		return $return;
	}


	protected function loadDueToRestart()
	{
		$q = 'SELECT '.self::FIELD_ID
			.'	,'.self::FIELD_USR_ID
			.'	,'.self::FIELD_ROOT_PRG_ID
			.'	,'.self::FIELD_LAST_CHANGE
			.'	,'.self::FIELD_LAST_CHANGE_BY
			.'	,'.self::FIELD_RESTART_DATE
			.'	,'.self::FIELD_RESTARTED_ASSIGNMENT_ID
			.'	FROM '.self::TABLE
			.'	WHERE '.self::FIELD_RESTARTED_ASSIGNMENT_ID.' = '
						.$this->db->quote(ilStudyProgrammeAssignment::NO_RESTARTED_ASSIGNMENT,'integer')
			.'		AND '.self::FIELD_RESTART_DATE.' IS NOT NULL'
			.'		AND DATE('.self::FIELD_RESTART_DATE.') <= '
						.$this->db->quote((new DateTime())->format(ilStudyProgrammeAssignment::DATE_FORMAT),'text');
		$res = $this->db->query($q);
		while($rec = $this->db->fetchAssoc($res)) {
			yield $rec;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function update(ilStudyProgrammeAssignment $assignment)
	{
		$row = [
			self::FIELD_ID => $assignment->getId(),
			self::FIELD_USR_ID => $assignment->getUserId(),
			self::FIELD_ROOT_PRG_ID => $assignment->getRootId(),
			self::FIELD_LAST_CHANGE_BY => $assignment->getLastChangeBy(),
			self::FIELD_LAST_CHANGE => $assignment->getLastChange()->format(ilStudyProgrammeAssignment::DATE_TIME_FORMAT),
			self::FIELD_RESTART_DATE => $assignment->getRestartDate() ? $assignment->getRestartDate()->format(ilStudyProgrammeAssignment::DATE_TIME_FORMAT) : null,
			self::FIELD_RESTARTED_ASSIGNMENT_ID => $assignment->getRestartedAssignmentId()
		];
		$this->updatedRowDB($row);
	}

	/**
	 * @inheritdoc
	 */
	public function delete(ilStudyProgrammeAssignment $assignment)
	{
		$this->deleteDB($assignment->getId());
	}

	protected function assignmentByRow(array $row) : ilStudyProgrammeAssignment
	{
		return (new ilStudyProgrammeAssignment($row[self::FIELD_ID]))
			->setRootId($row[self::FIELD_ROOT_PRG_ID])
			->setUserId($row[self::FIELD_USR_ID])
			->setLastChangeBy($row[self::FIELD_LAST_CHANGE_BY])
			->setLastChange(DateTime::createFromFormat(
				ilStudyProgrammeAssignment::DATE_TIME_FORMAT,$row[self::FIELD_LAST_CHANGE]))
			->setRestartDate(
				$row[self::FIELD_RESTART_DATE] ?
				DateTime::createFromFormat(ilStudyProgrammeAssignment::DATE_TIME_FORMAT,$row[self::FIELD_RESTART_DATE]) :
				null
			)
			->setRestartedAssignmentId($row[self::FIELD_RESTARTED_ASSIGNMENT_ID])
			->updateLastChange();
	}

	protected function loadByFilterDB(array $filter)
	{
		$q = 'SELECT '.self::FIELD_ID
			.'	,'.self::FIELD_USR_ID
			.'	,'.self::FIELD_ROOT_PRG_ID
			.'	,'.self::FIELD_LAST_CHANGE
			.'	,'.self::FIELD_LAST_CHANGE_BY
			.'	,'.self::FIELD_RESTART_DATE
			.'	,'.self::FIELD_RESTARTED_ASSIGNMENT_ID
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

	protected function insertRowDB(array $row)
	{
		$this->db->insert(
			self::TABLE,
			[
				self::FIELD_ID => ['interger',$row[self::FIELD_ID]]
				,self::FIELD_USR_ID => ['interger',$row[self::FIELD_USR_ID]]
				,self::FIELD_ROOT_PRG_ID => ['interger',$row[self::FIELD_ROOT_PRG_ID]]
				,self::FIELD_LAST_CHANGE => ['interger',$row[self::FIELD_LAST_CHANGE]]
				,self::FIELD_LAST_CHANGE_BY => ['interger',$row[self::FIELD_LAST_CHANGE_BY]]
				,self::FIELD_RESTART_DATE => ['timestamp',$row[self::FIELD_RESTART_DATE]]
				,self::FIELD_RESTARTED_ASSIGNMENT_ID => ['integer',$row[self::FIELD_RESTARTED_ASSIGNMENT_ID]]
			]
		);
	}

	protected function updatedRowDB(array $values)
	{
		$q = 'UPDATE '.self::TABLE
			.'	SET'
			.'	'.self::FIELD_USR_ID.' = '.$this->db->quote($values[self::FIELD_USR_ID],'integer')
			.'	,'.self::FIELD_ROOT_PRG_ID.' = '.$this->db->quote($values[self::FIELD_ROOT_PRG_ID],'integer')
			.'	,'.self::FIELD_LAST_CHANGE.' = '.$this->db->quote($values[self::FIELD_LAST_CHANGE],'text')
			.'	,'.self::FIELD_LAST_CHANGE_BY.' = '.$this->db->quote($values[self::FIELD_LAST_CHANGE_BY],'integer')
			.'	,'.self::FIELD_RESTART_DATE.' = '.$this->db->quote($values[self::FIELD_RESTART_DATE],'timestamp')
			.'	,'.self::FIELD_RESTARTED_ASSIGNMENT_ID.' = '.$this->db->quote($values[self::FIELD_RESTARTED_ASSIGNMENT_ID],'integer')
			.'	WHERE '.self::FIELD_ID.' = '.$this->db->quote($values[self::FIELD_ID],'integer');
		$this->db->manipulate($q);
	}

	protected function deleteDB(int $id)
	{
		$this->db->manipulate('DELETE FROM '.self::TABLE.' WHERE '.self::FIELD_ID.' = '.$this->db->quote($id,'integer'));
	}

	protected function nextId()
	{
		return $this->db->nextId(self::TABLE);
	}
}