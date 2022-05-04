<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class SuperiorPositionId
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SuperiorPositionId extends Base
{
    protected function run(array $params): int
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
    }

    public function getName() : string
    {
        return "getSuperiorPositionId";
    }

    /**
     * @return array
     */
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
        return "Returns the id of the default position 'Superior'";
    }
}
