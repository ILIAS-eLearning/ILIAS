<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitUserAssignmentQueries;

/**
 * Class UserIdsOfPositionAndOrgUnit
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserIdsOfPositionAndOrgUnit extends Base
{

    /**
     * @param array $params
     *
     * @return array
     */
    protected function run(array $params)
    {
        $position_id = $params[self::POSITION_ID];
        $orgu_id = $params[self::ORGU_REF_ID];

        return ilOrgUnitUserAssignmentQueries::getInstance()->getUserIdsOfOrgUnitsInPosition(array($orgu_id), $position_id);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getUserIdsOfPositionAndOrgUnit";
    }


    /**
     * @return array
     */
    protected function getAdditionalInputParams()
    {
        return array(
            self::POSITION_ID => Base::TYPE_INT,
            self::ORGU_REF_ID => Base::TYPE_INT,
        );
    }


    /**
     * @inheritdoc
     */
    public function getOutputParams()
    {
        return array('usr_ids' => Base::TYPE_INT_ARRAY);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns ids of users in a position of a given Org Unit";
    }
}
