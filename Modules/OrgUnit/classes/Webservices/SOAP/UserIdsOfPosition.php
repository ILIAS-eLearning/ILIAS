<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitUserAssignmentQueries;

/**
 * Class UserIdsOfPosition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserIdsOfPosition extends Base
{

    /**
     * @param array $params
     *
     * @return array
     */
    protected function run(array $params)
    {
        $position_id = $params[self::POSITION_ID];

        // $record = ilOrgUnitPosition::find($position_id);

        $usr_ids = [];
        foreach (ilOrgUnitUserAssignmentQueries::getInstance()->getUserAssignmentsOfPosition($position_id) as $assignment) {
            $usr_ids[] = $assignment->getUserId();
        }

        return $usr_ids;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getUserIdsOfPosition";
    }


    /**
     * @return array
     */
    protected function getAdditionalInputParams()
    {
        return array(self::POSITION_ID => Base::TYPE_INT);
    }


    /**
     * @inheritdoc
     */
    public function getOutputParams()
    {
        return array(self::USR_IDS => Base::TYPE_INT_ARRAY);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns ids of users in a position";
    }
}
