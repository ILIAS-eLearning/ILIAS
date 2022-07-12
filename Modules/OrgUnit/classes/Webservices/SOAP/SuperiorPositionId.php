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
