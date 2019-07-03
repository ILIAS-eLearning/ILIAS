<?php

declare(strict_types = 1);

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderFactory
{

	public function __construct(Pimple\Container $dic)
	{
		$this->dic = $dic;
	}

	/**
	 * Build a MembershipSourceReader according to $src_type.
	 *
	 * @throws InvalidArgumentException if $src_type is not one of the constant types in ilStudyProgrammeAutoMembershipSource.
	 */
	public function getReaderFor(string $src_type, int $src_id): ilStudyProgrammeMembershipSourceReader
	{
		switch ($src_type) {
			case ilStudyProgrammeAutoMembershipSource::TYPE_ROLE:
				return new ilStudyProgrammeMembershipSourceReaderRole(
					$this->dic['rbacreview'],
					$src_id
				);
			case ilStudyProgrammeAutoMembershipSource::TYPE_GROUP:
			case ilStudyProgrammeAutoMembershipSource::TYPE_COURSE:
				return new ilStudyProgrammeMembershipSourceReaderParticipants(
					ilParticipants::getInstance($src_id)
				);
			case ilStudyProgrammeAutoMembershipSource::TYPE_ORGU:
				return new ilStudyProgrammeMembershipSourceReaderOrgu(
					ilObjOrgUnitTree::_getInstance(),
					new ilOrgUnitUserAssignment(),
					$src_id
				);

			default:
				throw new \InvalidargumentException("Invalid source type.", 1);
		}
	}

}