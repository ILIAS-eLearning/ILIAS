<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class EmployeePositionId
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class EmployeePositionId extends Base
{

    /**
     * @param array $params
     * @return int
     */
    protected function run(array $params)
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return "getEmployeePositionId";
    }

    /**
     * @return array
     */
    protected function getAdditionalInputParams()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getOutputParams() : array
    {
        return array('position_id' => Base::TYPE_INT);
    }

    /**
     * @inheritdoc
     */
    public function getDocumentation() : string
    {
        return "Returns the id of the default position 'Employee'";
    }
}
