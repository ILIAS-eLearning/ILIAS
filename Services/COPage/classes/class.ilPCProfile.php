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
    protected php4DOMElement $prof_node;
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("prof");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->prof_node = $a_node->first_child();		// this is the profile node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->prof_node = $this->dom->create_element("Profile");
        $this->prof_node = $this->node->append_child($this->prof_node);
    }

    public function setFields(
        string $a_mode,
        array $a_fields = null
    ): void {
        $ilUser = $this->user;

        $this->prof_node->set_attribute("Mode", $a_mode);
        $this->prof_node->set_attribute("User", $ilUser->getId());

        // remove all children first
        $children = $this->prof_node->child_nodes();
        if ($children) {
            foreach ($children as $child) {
                $this->prof_node->remove_child($child);
            }
        }

        if ($a_mode == "manual") {
            foreach ($a_fields as $field) {
                $field_node = $this->dom->create_element("ProfileField");
                $field_node = $this->prof_node->append_child($field_node);
                $field_node->set_attribute("Name", $field);
            }
        }
    }

    public function getMode(): string
    {
        if (is_object($this->prof_node)) {
            return $this->prof_node->get_attribute("Mode");
        }
        return "";
    }

    /**
     * Get profile settings
     */
    public function getFields(): array
    {
        $res = array();
        if (is_object($this->prof_node)) {
            $children = $this->prof_node->child_nodes();
            if ($children) {
                foreach ($children as $child) {
                    $res[] = $child->get_attribute("Name");
                }
            }
        }
        return $res;
    }

    public static function getLangVars(): array
    {
        return array("pc_prof", "ed_insert_profile");
    }
}
