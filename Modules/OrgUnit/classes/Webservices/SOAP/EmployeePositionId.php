<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class EmployeePositionId
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class EmployeePositionId extends Base
{
    protected function run(array $params): int
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
    }

    public function getName() : string
    {
        return "getEmployeePositionId";
    }

    protected function getAdditionalInputParams(): array
    {
        return array();
    }

    public function getOutputParams() : array
    {
        return array('position_id' => Base::TYPE_INT);
    }

    public function getDocumentation() : string
    {
        return "Returns the id of the default position 'Employee'";
    }
}
