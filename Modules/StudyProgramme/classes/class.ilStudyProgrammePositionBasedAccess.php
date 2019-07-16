<?php declare(strict_types = 1);

class ilStudyProgrammePostionBasedAccess
{
	public function __construct(ilOrgUnitPositionAccessHandler $pah)
	{
		$this->pah = $pah;
	}

	public function getUsersInPrgAccessibleForOperation(ilObjStudyProgramme $prg, string $operation) : array
	{
		return array_map(
			function($val) {
				return (int)$val;
			},
			$this->pah->filterUserIdsByPositionOfCurrentUser($operation,$prg->getRefId(),$prg->getMembers())
		);
	}

	public function filterUsersAccessibleForOperation(ilObjStudyProgramme $prg, string $operation, array $user_ids) : array
	{
		return array_map(
			function($val) {
				return (int)$val;
			},
			$this->pah->filterUserIdsByPositionOfCurrentUser($operation,$prg->getRefId(),$user_ids)
		);
	}



	public function isUserAccessibleForOperationAtPrg(int $usr_id, ilObjStudyProgramme $prg, string $operation) : bool
	{
		return count($this->pah->filterUserIdsByPositionOfCurrentUser($operation,$prg->getRefId(),[$usr_id])) > 0;
	}
}