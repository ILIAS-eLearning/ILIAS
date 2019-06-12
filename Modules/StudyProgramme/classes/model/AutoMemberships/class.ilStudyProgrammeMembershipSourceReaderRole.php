<?php

declare(strict_types = 1);

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderRole implements ilStudyProgrammeMembershipSourceReader
{
	public function __construct(ilRbacReview $rbac_review, int $src_id)
	{
		$this->src_id = $src_id;
		$this->rbac_review = $rbac_review;
	}

	/**
	 * @inheritdoc
	 */
	public function getMemberIds(): array
	{
		return array_map(
			'intval',
			$this->rbac_review->assignedUsers($this->src_id)
		);
	}
}
