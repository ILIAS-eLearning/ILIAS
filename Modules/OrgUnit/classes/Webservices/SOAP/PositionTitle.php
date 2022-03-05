<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitPosition;

/**
 * Class PositionTitle
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PositionTitle extends Base
{
    public const POSITION_ID = 'position_id';

    /**
     * @throws \SoapFault
     */
    final protected function run(array $params): string
    {
        $position_id = $params[self::POSITION_ID];

        $record = ilOrgUnitPosition::find($position_id);
        if ($record instanceof ilOrgUnitPosition) {
            return $record->getTitle();
        } else {
            $this->addError("Position with id {$position_id} not found");
        }
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return "getPositionTitle";
    }

    final protected function getAdditionalInputParams(): array
    {
        return array(self::POSITION_ID => Base::TYPE_INT);
    }

    final public function getOutputParams() : array
    {
        return array('title' => Base::TYPE_STRING);
    }

    final public function getDocumentation() : string
    {
        return "Returns the title of a position for a given position id";
    }
}
