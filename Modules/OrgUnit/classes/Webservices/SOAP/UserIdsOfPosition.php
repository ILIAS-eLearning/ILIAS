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
 ********************************************************************
 */

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitUserAssignmentQueries;

/**
 * Class UserIdsOfPosition
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserIdsOfPosition extends Base
{
    /**
     * @return int[]
     */
    protected function run(array $params): array
    {
        $position_id = $params[self::POSITION_ID];

        // $record = ilOrgUnitPosition::find($position_id);

        $usr_ids = [];
        foreach (ilOrgUnitUserAssignmentQueries::getInstance()->getUserAssignmentsOfPosition($position_id) as $assignment) {
            $usr_ids[] = $assignment->getUserId();
        }

        return $usr_ids;
    }

    public function getName(): string
    {
        return "getUserIdsOfPosition";
    }

    protected function getAdditionalInputParams(): array
    {
        return array(self::POSITION_ID => Base::TYPE_INT);
    }

    public function getOutputParams(): array
    {
        return array(self::USR_IDS => Base::TYPE_INT_ARRAY);
    }

    public function getDocumentation(): string
    {
        return "Returns ids of users in a position";
    }
}
