<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class PositionIds
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PositionIds extends Base
{
    final protected function run(array $params): array
    {
        return ilOrgUnitPosition::getArray(null, 'id');
    }

    public function getName() : string
    {
        return "getPositionIds";
    }

    final protected function getAdditionalInputParams(): array
    {
        return array();
    }

    final public function getOutputParams() : array
    {
        return array('position_ids' => Base::TYPE_INT_ARRAY);
    }

    public function getDocumentation() : string
    {
        return "Returns an array of all existing position ids";
    }
}
