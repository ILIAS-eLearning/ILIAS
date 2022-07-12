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
    protected php4DOMElement $cach_node;
    protected ilObjUser $user;

    public function init() : void
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->setType("cach");
    }
    
    public static function getLangVars() : array
    {
        return array("ed_insert_consultation_hours", "pc_cach");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->cach_node = $a_node->first_child();		// this is the consultation hours node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->cach_node = $this->dom->create_element("ConsultationHours");
        $this->cach_node = $this->node->append_child($this->cach_node);
    }

    /**
     * Set consultation hours settings
     */
    public function setData(
        string $a_mode,
        array $a_grp_ids
    ) : void {
        $ilUser = $this->user;
        
        $this->cach_node->set_attribute("Mode", $a_mode);
        $this->cach_node->set_attribute("User", $ilUser->getId());
        
        // remove all children first
        $children = $this->cach_node->child_nodes();
        if ($children) {
            foreach ($children as $child) {
                $this->cach_node->remove_child($child);
            }
        }

        if ($a_mode === "manual") {
            foreach ($a_grp_ids as $grp_id) {
                $field_node = $this->dom->create_element("ConsultationHoursGroup");
                $field_node = $this->cach_node->append_child($field_node);
                $field_node->set_attribute("Id", $grp_id);
            }
        }
    }

    /**
     * Get consultation hours group ids
     */
    public function getGroupIds() : array
    {
        $res = array();
        if (is_object($this->cach_node)) {
            $children = $this->cach_node->child_nodes();
            if ($children) {
                foreach ($children as $child) {
                    $res[] = $child->get_attribute("Id");
                }
            }
        }
        return $res;
    }
}
