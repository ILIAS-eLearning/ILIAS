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
 * Class ilPCProfile
 * Personal data content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCProfile extends ilPageContent
{
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("prof");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Profile");
    }

    public function setFields(
        string $a_mode,
        array $a_fields = null
    ): void {
        $ilUser = $this->user;

        $this->getChildNode()->setAttribute("Mode", $a_mode);
        $this->getChildNode()->setAttribute("User", $ilUser->getId());

        // remove all children first
        $this->dom_util->deleteAllChilds($this->getChildNode());

        if ($a_mode == "manual") {
            foreach ($a_fields as $field) {
                $field_node = $this->dom_doc->createElement("ProfileField");
                $field_node = $this->getChildNode()->appendChild($field_node);
                $field_node->setAttribute("Name", $field);
            }
        }
    }

    public function getMode(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute("Mode");
        }
        return "";
    }

    /**
     * Get profile settings
     */
    public function getFields(): array
    {
        $res = array();
        if (is_object($this->getChildNode())) {
            foreach ($this->getChildNode()->childNodes as $child) {
                $res[] = $child->getAttribute("Name");
            }
        }
        return $res;
    }

    public static function getLangVars(): array
    {
        return array("pc_prof", "ed_insert_profile");
    }
}
