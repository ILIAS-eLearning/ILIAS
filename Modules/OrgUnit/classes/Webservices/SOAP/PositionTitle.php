<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    protected function run(array $params): string
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
    public function getName(): string
    {
        return "getPositionTitle";
    }

    protected function getAdditionalInputParams(): array
    {
        return array(self::POSITION_ID => Base::TYPE_INT);
    }

    public function getOutputParams(): array
    {
        return array('title' => Base::TYPE_STRING);
    }

    public function getDocumentation(): string
    {
        return "Returns the title of a position for a given position id";
    }
}
