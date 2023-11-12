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
 *********************************************************************/

/**
 * Class ilPCConsultationHours
 * Consultation hours content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCConsultationHours extends ilPageContent
{
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->setType("cach");
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_consultation_hours", "pc_cach");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $cach_node = $this->dom_doc->createElement("ConsultationHours");
        $cach_node = $this->getDomNode()->appendChild($cach_node);
    }

    /**
     * Set consultation hours settings
     */
    public function setData(
        string $a_mode,
        array $a_grp_ids
    ): void {
        $ilUser = $this->user;

        $this->getChildNode()->setAttribute("Mode", $a_mode);
        $this->getChildNode()->setAttribute("User", $ilUser->getId());

        // remove all children first
        $this->dom_util->deleteAllChilds($this->getChildNode());

        if ($a_mode === "manual") {
            foreach ($a_grp_ids as $grp_id) {
                $field_node = $this->dom_doc->createElement("ConsultationHoursGroup");
                $field_node = $this->getChildNode()->appendChild($field_node);
                $field_node->setAttribute("Id", $grp_id);
            }
        }
    }

    /**
     * Get consultation hours group ids
     */
    public function getGroupIds(): array
    {
        $res = array();
        if (is_object($this->getChildNode())) {
            foreach ($this->getChildNode()->childNodes as $child) {
                $res[] = $child->getAttribute("Id");
            }
        }
        return $res;
    }
}
