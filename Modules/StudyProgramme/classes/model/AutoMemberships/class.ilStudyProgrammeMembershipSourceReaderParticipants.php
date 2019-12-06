<?php

declare(strict_types = 1);

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderParticipants implements ilStudyProgrammeMembershipSourceReader
{
	public function __construct(ilParticipants $participants)
	{
		$this->participants = $participants;
	}

	/**
	 * @inheritdoc
	 */
	public function getMemberIds(): array
	{
		return array_map(
			'intval',
			$this->participants->getMembers()
		);
	}
}
