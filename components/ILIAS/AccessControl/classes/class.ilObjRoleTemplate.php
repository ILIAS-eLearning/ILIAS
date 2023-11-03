<?php

declare(strict_types=1);
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
 *********************************************************************/

/**
 * Class ilObjRoleTemplate
 * @author     Stefan Meyer <meyer@leifos.com>
 * @ingroup    ServicesAccessControl
 */
class ilObjRoleTemplate extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = false)
    {
        $this->type = "rolt";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function getPresentationTitle(): string
    {
        $r = ilObjRole::_getTranslation($this->getTitle());

        if ($r === $this->getUntranslatedTitle()) {
            return $r;
        }

        return $r . ' (' . $this->getUntranslatedTitle() . ')';
    }

    /**
    * delete role template and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete(): bool
    {
        // put here role template specific stuff
        // delete rbac permissions
        $this->rbac_admin->deleteTemplate($this->getId());

        // always call parent delete function at the end!!
        return parent::delete();
    }

    public function isInternalTemplate(): bool
    {
        if (substr($this->getTitle(), 0, 3) == "il_") {
            return true;
        }

        return false;
    }

    public function getFilterOfInternalTemplate(): array
    {
        $filter = [];
        switch ($this->getTitle()) {
            case "il_grp_admin":
            case "il_grp_member":
            case "il_grp_status_closed":
            case "il_grp_status_open":
                $obj_data = $this->obj_definition->getSubObjects('grp', false);
                unset($obj_data["rolf"]);
                $filter = array_keys($obj_data);
                $filter[] = 'grp';
                break;

            case "il_crs_admin":
            case "il_crs_tutor":
            case "il_crs_member":
            case "il_crs_non_member":
                $obj_data = $this->obj_definition->getSubObjects('crs', false);
                unset($obj_data["rolf"]);
                $filter = array_keys($obj_data);
                $filter[] = 'crs';
                break;
            case "il_frm_moderator":
                $filter[] = 'frm';
                break;
            case "il_chat_moderator":
                $filter[] = 'chtr';
                break;
        }

        return $filter;
    }
}
