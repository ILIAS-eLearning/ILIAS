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

use ilObject2;
use ilObjUser;
use ilOrgUnitPosition;

/**
 * Class AddUserIdToPositionInOrgUnit
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class AddUserIdToPositionInOrgUnit extends Base
{
    protected function run(array $params): bool
    {
        $position_id = $params[self::POSITION_ID];
        $user_id = $params[self::USR_ID];
        $orgu_ref_id = $params[self::ORGU_REF_ID];

        if (!ilObjUser::_exists($user_id)) {
            $this->addError("user does not exist");
        } elseif (!ilOrgUnitPosition::find($position_id) instanceof ilOrgUnitPosition) {
            $this->addError("Position does not exist");
        } elseif (ilObject2::_lookupType($orgu_ref_id, true) !== 'orgu') {
            $this->addError("OrgUnit does not exist");
        } else {
            \ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $orgu_ref_id);
        }

        return true;
    }

    public function getName(): string
    {
        return "addUserToPositionInOrgUnit";
    }

    protected function getAdditionalInputParams(): array
    {
        return array(self::POSITION_ID => Base::TYPE_INT,
                     self::USR_ID => Base::TYPE_INT,
                     self::ORGU_REF_ID => Base::TYPE_INT
        );
    }

    public function getOutputParams(): array
    {
        return [];
    }

    public function getDocumentation(): string
    {
        return "Adds a user to a position in a orgunit";
    }
}
