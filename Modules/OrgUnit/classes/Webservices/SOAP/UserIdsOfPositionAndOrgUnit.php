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

use ilOrgUnitUserAssignmentQueries;

/**
 * Class UserIdsOfPositionAndOrgUnit
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class UserIdsOfPositionAndOrgUnit extends Base
{
    protected function run(array $params): array
    {
        $position_id = $params[self::POSITION_ID];
        $orgu_id = $params[self::ORGU_REF_ID];

        return ilOrgUnitUserAssignmentQueries::getInstance()->getUserIdsOfOrgUnitsInPosition(array($orgu_id),
            $position_id);
    }

    public function getName() : string
    {
        return "getUserIdsOfPositionAndOrgUnit";
    }

    protected function getAdditionalInputParams(): array
    {
        return array(
            self::POSITION_ID => Base::TYPE_INT,
            self::ORGU_REF_ID => Base::TYPE_INT,
        );
    }

    public function getOutputParams() : array
    {
        return array('usr_ids' => Base::TYPE_INT_ARRAY);
    }

    public function getDocumentation() : string
    {
        return "Returns ids of users in a position of a given Org Unit";
    }
}
