<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class SuperiorPositionId
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SuperiorPositionId extends Base
{
    final protected function run(array $params): int
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
    }

    final public function getName() : string
    {
        return "getSuperiorPositionId";
    }

    /**
     * @return array
     */
    final protected function getAdditionalInputParams(): array
    {
        return array();
    }

    final public function getOutputParams() : array
    {
        return array('position_id' => Base::TYPE_INT);
    }

    final public function getDocumentation() : string
    {
        return "Returns the id of the default position 'Superior'";
    }
}
