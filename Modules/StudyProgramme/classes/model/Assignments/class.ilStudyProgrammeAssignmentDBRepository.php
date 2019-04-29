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
			self::FIELD_LAST_CHANGE => ilUtil::now()
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
	public function update(ilStudyProgrammeAssignment $assignment)
	{
		$row = [
			self::FIELD_ID => $assignment->getId(),
			self::FIELD_USR_ID => $assignment->getUserId(),
			self::FIELD_ROOT_PRG_ID => $assignment->getRootId(),
			self::FIELD_LAST_CHANGE_BY => $assignment->getLastChangeBy(),
			self::FIELD_LAST_CHANGE => $assignment->getLastChange()->get(IL_CAL_DATETIME)
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
			->setLastChange(new ilDateTime($row[self::FIELD_LAST_CHANGE], IL_CAL_DATETIME))
			->updateLastChange();
	}

	protected function loadByFilterDB(array $filter) 
	{
		$q = 'SELECT '.self::FIELD_ID
			.'	,'.self::FIELD_USR_ID
			.'	,'.self::FIELD_ROOT_PRG_ID
			.'	,'.self::FIELD_LAST_CHANGE
			.'	,'.self::FIELD_LAST_CHANGE_BY
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