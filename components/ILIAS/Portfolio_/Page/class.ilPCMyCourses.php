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

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $mcrs_node = $this->dom_doc->createElement("MyCourses");
        $mcrs_node = $this->getDomNode()->appendChild($mcrs_node);
    }

    /**
     * Set courses settings
     */
    public function setData(string $a_sort): void
    {
        $ilUser = $this->user;
        $this->getChildNode()->setAttribute("User", $ilUser->getId());
        $this->getChildNode()->setAttribute("Sort", $a_sort);
    }

    public function getSorting(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute("Sort");
        }
        return "";
    }
}
