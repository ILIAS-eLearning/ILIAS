<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class SuperiorPositionId
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SuperiorPositionId extends Base
{

    /**
     * @param array $params
     *
     * @return int
     */
    protected function run(array $params)
    {
        return ilOrgUnitPosition::getCorePositionId(ilOrgUnitPosition::CORE_POSITION_SUPERIOR);
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getSuperiorPositionId";
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
    public function getOutputParams()
    {
        return array('position_id' => Base::TYPE_INT);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns the id of the default position 'Superior'";
    }
}
