<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilObject2;
use ilObjUser;
use ilOrgUnitPosition;
use LogicException;

/**
 * Class AddUserIdToPositionInOrgUnit
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RemoveUserIdFromPositionInOrgUnit extends Base
{

    /**
     * @param array $params
     *
     * @return mixed|void
     * @throws \ilSoapPluginException
     */
    protected function run(array $params)
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
                    'user_id'     => $user_id,
                    'position_id' => $position_id,
                    'orgu_id'     => $orgu_ref_id,
                )
            )->first();
            if ($inst instanceof \ilOrgUnitUserAssignment) {
                $inst->delete();
            } else {
                $this->error("No assignment found");
            }
        }
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "removeUserFromPositionInOrgUnit";
    }


    /**
     * @return array
     */
    protected function getAdditionalInputParams()
    {
        return array(self::POSITION_ID => Base::TYPE_INT, self::USR_ID => Base::TYPE_INT, self::ORGU_REF_ID => Base::TYPE_INT);
    }


    /**
     * @inheritdoc
     */
    public function getOutputParams()
    {
        return [];
    }


    /**
     * @inheritdoc
     */
    public function getDocumentation()
    {
        return "Removes a user from a position in a orgunit";
    }
}
