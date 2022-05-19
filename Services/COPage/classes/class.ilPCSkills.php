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
 * Class ilPCSkills
 * Skills content object (see ILIAS DTD)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCSkills extends ilPageContent
{
    protected php4DOMElement $skill_node;
    protected ilObjUser $user;

    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("skills");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->skill_node = $a_node->first_child();		// this is the skill node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->skill_node = $this->dom->create_element("Skills");
        $this->skill_node = $this->node->append_child($this->skill_node);
    }

    public function setData(string $a_skill_id) : void
    {
        $ilUser = $this->user;
        
        $this->skill_node->set_attribute("Id", $a_skill_id);
        $this->skill_node->set_attribute("User", $ilUser->getId());
    }

    public function getSkillId() : string
    {
        if (is_object($this->skill_node)) {
            return $this->skill_node->get_attribute("Id");
        }
        return "";
    }
    
    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ) : void {
        // pc skill
        self::saveSkillUsage($a_page, $a_domdoc);
    }
    
    /**
     * Before page is being deleted
     */
    public static function beforePageDelete(
        ilPageObject $a_page
    ) : void {
        ilPageContentUsage::deleteAllUsages(
            "skmg",
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            false,
            $a_page->getLanguage()
        );
    }

    /**
     * After page history entry has been created
     */
    public static function afterPageHistoryEntry(
        ilPageObject $a_page,
        DOMDocument $a_old_domdoc,
        string $a_old_xml,
        int $a_old_nr
    ) : void {
        self::saveSkillUsage($a_page, $a_old_domdoc, $a_old_nr);
    }
    
    public static function saveSkillUsage(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        int $a_old_nr = 0
    ) : void {
        $skl_ids = self::collectSkills($a_page, $a_domdoc);
        ilPageContentUsage::deleteAllUsages(
            "skmg",
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            $a_old_nr,
            $a_page->getLanguage()
        );
        foreach ($skl_ids as $skl_id) {
            if ((int) $skl_id["inst_id"] <= 0) {
                ilPageContentUsage::saveUsage(
                    "skmg",
                    $skl_id["id"],
                    $a_page->getParentType() . ":pg",
                    $a_page->getId(),
                    $a_old_nr,
                    $a_page->getLanguage()
                );
            }
        }
    }

    public static function collectSkills(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ) : array {
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//Skills');

        $skl_ids = array();
        foreach ($nodes as $node) {
            $user = $node->getAttribute("User");
            $id = $node->getAttribute("Id");
            $inst_id = $node->getAttribute("InstId");
            $skl_ids[$user . ":" . $id . ":" . $inst_id] = array(
                "user" => $user, "id" => $id, "inst_id" => $inst_id);
        }

        return $skl_ids;
    }

    public static function deleteHistoryLowerEqualThan(
        string $parent_type,
        int $page_id,
        string $lang,
        int $delete_lower_than_nr
    ) : void {
        global $DIC;

        $usage_repo = $DIC->copage()
                              ->internal()
                              ->repo()
                              ->usage();

        $usage_repo->deleteHistoryUsagesLowerEqualThan(
            "skmg",
            $parent_type . ":pg",
            $page_id,
            $delete_lower_than_nr,
            $lang
        );
    }
}
