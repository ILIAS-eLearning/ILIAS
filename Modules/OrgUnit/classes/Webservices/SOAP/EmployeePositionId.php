<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class EmployeePositionId
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class EmployeePositionId extends Base
{
    final protected function run(array $params): int
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);
    }

    final public function getName() : string
    {
        return "getEmployeePositionId";
    }

    final protected function getAdditionalInputParams(): array
    {
        return array();
    }

    final public function getOutputParams() : array
    {
        return array('position_id' => Base::TYPE_INT);
    }

    public function getDocumentation() : string
    {
        return "Returns the id of the default position 'Employee'";
    }
}
