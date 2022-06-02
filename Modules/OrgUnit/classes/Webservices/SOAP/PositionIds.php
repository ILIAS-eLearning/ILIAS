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
 * Class PositionIds
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PositionIds extends Base
{
    protected function run(array $params): array
    {
        return ilOrgUnitPosition::getArray(null, 'id');
    }

    public function getName() : string
    {
        return "getPositionIds";
    }

    protected function getAdditionalInputParams(): array
    {
        return array();
    }

    public function getOutputParams() : array
    {
        return array('position_ids' => Base::TYPE_INT_ARRAY);
    }

    public function getDocumentation() : string
    {
        return "Returns an array of all existing position ids";
    }
}
