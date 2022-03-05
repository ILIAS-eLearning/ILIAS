<?php

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
    final protected function run(array $params) : array
    {
        $position_id = $params[self::POSITION_ID];

        // $record = ilOrgUnitPosition::find($position_id);

        $usr_ids = [];
        foreach (ilOrgUnitUserAssignmentQueries::getInstance()->getUserAssignmentsOfPosition($position_id) as $assignment) {
            $usr_ids[] = $assignment->getUserId();
        }

        return $usr_ids;
    }

    public function getName() : string
    {
        return "getUserIdsOfPosition";
    }

    final protected function getAdditionalInputParams() : array
    {
        return array(self::POSITION_ID => Base::TYPE_INT);
    }

    final public function getOutputParams() : array
    {
        return array(self::USR_IDS => Base::TYPE_INT_ARRAY);
    }

    public function getDocumentation() : string
    {
        return "Returns ids of users in a position";
    }
}
