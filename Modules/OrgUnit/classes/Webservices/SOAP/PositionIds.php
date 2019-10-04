<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class PositionIds
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PositionIds extends Base
{

    /**
     * @param array $params
     *
     * @return array
     */
    protected function run(array $params)
    {
        return ilOrgUnitPosition::getArray(null, 'id');
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getPositionIds";
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
        return array('position_ids' => Base::TYPE_INT_ARRAY);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns an array of all existing position ids";
    }
}
