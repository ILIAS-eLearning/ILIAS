<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderFactory
{
    protected Pimple\Container  $dic;

    public function __construct(Pimple\Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * Build a MembershipSourceReader according to $src_type.
     *
     * @throws InvalidArgumentException if $src_type is not one of the constant types in ilStudyProgrammeAutoMembershipSource.
     */
    public function getReaderFor(ilStudyProgrammeAutoMembershipSource $ams, int $exclude_id): ilStudyProgrammeMembershipSourceReader
    {
        $src_id = $ams->getSourceId();
        switch ($ams->getSourceType()) {
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
                    $src_id,
                    $ams->isSearchRecursive(),
                    $exclude_id
                );

            default:
                throw new InvalidargumentException("Invalid source type.", 1);
        }
    }
}
