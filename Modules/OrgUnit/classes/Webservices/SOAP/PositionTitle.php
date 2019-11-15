<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class PositionTitle
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PositionTitle extends Base
{

    /**
     * @param array $params
     *
     * @return array
     */
    const POSITION_ID = 'position_id';


    protected function run(array $params)
    {
        $position_id = $params[self::POSITION_ID];

        $record = ilOrgUnitPosition::find($position_id);
        if ($record instanceof ilOrgUnitPosition) {
            return $record->getTitle();
        } else {
            $this->error("Position with id {$position_id} not found");
        }
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "getPositionTitle";
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
        return array('title' => Base::TYPE_STRING);
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Returns the title of a position for a given position id";
    }
}
