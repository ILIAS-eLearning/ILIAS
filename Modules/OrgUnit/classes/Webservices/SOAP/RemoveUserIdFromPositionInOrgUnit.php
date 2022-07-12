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
use LogicException;
use SoapFault;

/**
 * Class AddUserIdToPositionInOrgUnit
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RemoveUserIdFromPositionInOrgUnit extends Base
{

    /**
     * @throws SoapFault
     */
    protected function run(array $params): bool
    {
        $position_id = $params[self::POSITION_ID];
        $user_id = $params[self::USR_ID];
        $orgu_ref_id = $params[self::ORGU_REF_ID];

        if (!ilObjUser::_exists($user_id)) {
            throw new LogicException("User does not exist");
        }
        if (!ilOrgUnitPosition::find($position_id) instanceof ilOrgUnitPosition) {
            throw new LogicException("Position does not exist");
        }
        if (ilObject2::_lookupType($orgu_ref_id, true) !== 'orgu') {
            throw new LogicException("OrgUnit does not exist");
        } else {
            $inst = \ilOrgUnitUserAssignment::where(
                array(
                    'user_id' => $user_id,
                    'position_id' => $position_id,
                    'orgu_id' => $orgu_ref_id,
                )
            )->first();
            if ($inst instanceof \ilOrgUnitUserAssignment) {
                $inst->delete();
            } else {
                $this->addError("No assignment found");
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return "removeUserFromPositionInOrgUnit";
    }

    protected function getAdditionalInputParams(): array
    {
        return array(self::POSITION_ID => Base::TYPE_INT,
                     self::USR_ID => Base::TYPE_INT,
                     self::ORGU_REF_ID => Base::TYPE_INT
        );
    }

    public function getOutputParams() : array
    {
        return [];
    }

    public function getDocumentation() : string
    {
        return "Removes a user from a position in a orgunit";
    }
}
