<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilObject2;
use ilObjUser;
use ilOrgUnitPosition;

/**
 * Class AddUserIdToPositionInOrgUnit
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class AddUserIdToPositionInOrgUnit extends Base
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
            $this->error("user does not exist");
        } elseif (!ilOrgUnitPosition::find($position_id) instanceof ilOrgUnitPosition) {
            $this->error("Position does not exist");
        } elseif (ilObject2::_lookupType($orgu_ref_id, true) !== 'orgu') {
            $this->error("OrgUnit does not exist");
        } else {
            \ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $orgu_ref_id);
        }
    }


    /**
     * @return string
     */
    public function getName()
    {
        return "addUserToPositionInOrgUnit";
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
        return "Adds a user to a position in a orgunit";
    }
}
