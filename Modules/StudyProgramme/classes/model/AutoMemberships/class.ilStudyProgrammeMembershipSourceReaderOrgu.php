<?php

declare(strict_types = 1);

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderOrgu implements ilStudyProgrammeMembershipSourceReader
{
	public function __construct(
		ilObjOrgUnitTree $orgu_tree,
		ilOrgUnitUserAssignment $orgu_assignment,
		int $src_id
	){
		$this->orgu_tree = $orgu_tree;
		$this->orgu_assignment = $orgu_assignment;
		$this->src_id = $src_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getMemberIds(): array
	{
		$assignees = $this->orgu_assignment::where(
			['orgu_id'=>$this->src_id]
		)->getArray('id', 'user_id');

		return array_map(
			'intval',
			array_values($assignees)
		);
	}
}
