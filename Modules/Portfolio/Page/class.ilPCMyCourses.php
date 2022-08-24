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
 * Class ilPCMyCourses
 * My courses content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCMyCourses extends ilPageContent
{
    protected php4DOMElement $mcrs_node;
    protected ilObjUser $user;

    public function init(): void
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->setType("mcrs");
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_my_courses", "pc_mcrs");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->mcrs_node = $a_node->first_child();		// this is the courses node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->mcrs_node = $this->dom->create_element("MyCourses");
        $this->mcrs_node = $this->node->append_child($this->mcrs_node);
    }

    /**
     * Set courses settings
     */
    public function setData(string $a_sort): void
    {
        $ilUser = $this->user;
        $this->mcrs_node->set_attribute("User", $ilUser->getId());
        $this->mcrs_node->set_attribute("Sort", $a_sort);
    }

    public function getSorting(): string
    {
        if (is_object($this->mcrs_node)) {
            return $this->mcrs_node->get_attribute("Sort");
        }
        return "";
    }
}
