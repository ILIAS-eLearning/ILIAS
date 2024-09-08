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
 * Portfolio base
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilObjPortfolioBase extends ilObject2
{
    protected \ILIAS\Notes\Service $notes;
    protected ilSetting $setting;
    protected bool $online = false;
    protected bool $comments = false;
    protected string $bg_color = "";
    protected string $font_color = "";
    protected string $img = "";
    protected string $ppic = "";
    protected bool $style = false;

    public function __construct(
        int $a_id = 0,
        bool $a_reference = true
    ) {
        global $DIC;

        $this->notes = $DIC->notes();

        parent::__construct($a_id, $a_reference);

        $this->setting = $DIC->settings();

        $this->db = $DIC->database();
    }


    //
    // PROPERTIES
    //

    public function setOnline(bool $a_value): void
    {
        $this->online = $a_value;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public static function lookupOnline(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT is_online" .
            " FROM usr_portfolio" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return  (bool) $row["is_online"];
    }

    public function setPublicComments(bool $a_value): void
    {
        $this->comments = $a_value;
    }

    public function hasPublicComments(): bool
    {
        return $this->comments;
    }

    public function hasProfilePicture(): bool
    {
        return $this->ppic;
    }

    public function setProfilePicture(bool $a_status): void
    {
        $this->ppic = $a_status;
    }

    public function getBackgroundColor(): string
    {
        if (!$this->bg_color) {
            $this->bg_color = "ffffff";
        }
        return $this->bg_color;
    }

    /**
     * Set background color, e.g. "efefef"
     */
    public function setBackgroundColor(string $a_value): void
    {
        $this->bg_color = $a_value;
    }

    public function getFontColor(): string
    {
        if (!$this->font_color) {
            $this->font_color = "505050";
        }
        return $this->font_color;
    }

    public function setFontColor(string $a_value): void
    {
        $this->font_color = $a_value;
    }

    //
    // CRUD
    //

    protected function doRead(): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT * FROM usr_portfolio" .
                " WHERE id = " . $ilDB->quote($this->id, "integer"));
        $row = $ilDB->fetchAssoc($set);

        $this->setOnline((bool) $row["is_online"]);
        $this->setProfilePicture((bool) $row["ppic"]);
        $this->setBackgroundColor((string) $row["bg_color"]);
        $this->setFontColor((string) $row["font_color"]);

        // #14661
        $this->setPublicComments($this->notes->domain()->commentsActive($this->id));

        $this->doReadCustom($row);
    }

    /**
     * May be overwritten by derived classes
     */
    protected function doReadCustom(array $a_row): void
    {
    }

    protected function doCreate(bool $clone_mode = false): void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("INSERT INTO usr_portfolio (id,is_online)" .
            " VALUES (" . $ilDB->quote($this->id, "integer") . "," .
            $ilDB->quote(0, "integer") . ")");
    }

    protected function doUpdate(): void
    {
        $ilDB = $this->db;

        $fields = array(
            "is_online" => array("integer", $this->isOnline()),
            "ppic" => array("integer", $this->hasProfilePicture()),
            "bg_color" => array("text", $this->getBackgroundColor()),
            "font_color" => array("text", $this->getFontColor())
        );
        $this->doUpdateCustom($fields);

        // #14661
        $this->notes->domain()->activateComments($this->id, $this->hasPublicComments());

        $ilDB->update(
            "usr_portfolio",
            $fields,
            array("id" => array("integer", $this->id))
        );
    }

    /**
     * May be overwritte by derived classes
     */
    protected function doUpdateCustom(array &$a_fields): void
    {
    }

    protected function doDelete(): void
    {
        $ilDB = $this->db;

        $this->deleteAllPages();
        $this->deleteImage();

        $ilDB->manipulate("DELETE FROM usr_portfolio" .
            " WHERE id = " . $ilDB->quote($this->id, "integer"));
    }

    abstract protected function deleteAllPages(): void;


    //
    // TRANSMOGRIFIER
    //

    /**
     * Clone basic settings
     *
     * @param ilObjPortfolioBase $a_source
     * @param ilObjPortfolioBase $a_target
     */
    protected static function cloneBasics(
        ilObjPortfolioBase $a_source,
        ilObjPortfolioBase $a_target
    ): void {
        global $DIC;

        // copy portfolio properties
        $a_target->setPublicComments($a_source->hasPublicComments());
        $a_target->setProfilePicture($a_source->hasProfilePicture());
        $a_target->setFontColor($a_source->getFontColor());
        $a_target->setBackgroundColor($a_source->getBackgroundColor());
        $a_target->update();

        // container settings
        foreach (ilContainer::_getContainerSettings($a_source->getId()) as $keyword => $value) {
            ilContainer::_writeContainerSetting($a_target->getId(), $keyword, $value);
        }

        // style
        $content_style_domain = $DIC
            ->contentStyle()
            ->domain()
            ->styleForObjId($a_source->getId());
        $content_style_domain->cloneTo($a_target->getId());
    }

    /**
     * Build template from portfolio and vice versa
     */
    public static function clonePagesAndSettings(
        ilObjPortfolioBase $a_source,
        ilObjPortfolioBase $a_target,
        ?array $a_recipe = null,
        bool $copy_all = false
    ): void {
        global $DIC;

        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $skill_personal_service = $DIC->skills()->personal();

        $source_id = $a_source->getId();
        $target_id = $a_target->getId();

        if ($a_source instanceof ilObjPortfolioTemplate &&
            $a_target instanceof ilObjPortfolio) {
            $direction = "t2p";
        } elseif ($a_source instanceof ilObjPortfolio &&
            $a_target instanceof ilObjPortfolioTemplate) {
            $direction = "p2t";
        } else {
            return;
        }

        self::cloneBasics($a_source, $a_target);

        // copy advanced metadata
        $copy_id = ilCopyWizardOptions::_allocateCopyId();
        ilAdvancedMDValues::_cloneValues($copy_id, $a_source->getId(), $a_target->getId());

        // copy selection of global optional sets
        ilAdvancedMDRecord::saveObjRecSelection(
            $a_target->getId(),
            'pfpg',
            ilAdvancedMDRecord::getObjRecSelection($a_source->getId(), 'pfpg')
        );

        // fix metadata record type assignment
        // e.g. if portfolio is created from template
        // we need to change this from prtt to prtf
        foreach (\ilAdvancedMDRecord::_getSelectedRecordsByObject(
            ilObject::_lookupType($a_source->getId()),
            $a_target->getId(),
            "pfpg",
            false
        ) as $rec) {
            /*
             * BT 35494: reset assignement of the newly cloned local records,
             * and only append what's needed to global ones
             */
            $target_type = ilObject::_lookupType($a_target->getId());
            if ($rec->getParentObject() == $a_target->getId()) {
                $rec->setAssignedObjectTypes(
                    [[
                         "obj_type" => $target_type,
                         "sub_type" => "pfpg",
                         "optional" => 0
                     ]
                    ]
                );
            } elseif (!$rec->isAssignedObjectType($target_type, 'pfpg')) {
                $rec->appendAssignedObjectType(
                    $target_type,
                    "pfpg"
                );
            }
            $rec->update();
        }

        // personal skills
        $pskills = array_keys($skill_personal_service->getSelectedUserSkills($ilUser->getId()));

        // copy pages
        $page_map = array();
        foreach (ilPortfolioPage::getAllPortfolioPages($source_id) as $page) {
            $page_id = $page["id"];

            if ($direction === "t2p") {
                $source_page = new ilPortfolioTemplatePage($page_id);
                $target_page = new ilPortfolioPage();
            } else {
                $source_page = new ilPortfolioPage($page_id);
                $target_page = new ilPortfolioTemplatePage();
            }
            $source_page->setPortfolioId($source_id);
            $target_page->setPortfolioId($target_id);

            $page_type = $source_page->getType();
            $page_title = $source_page->getTitle();




            $page_recipe = null;
            if (isset($a_recipe)) {
                $page_recipe = $a_recipe[$page_id] ?? null;
            }

            $valid = false;
            switch ($page_type) {
                // page editor
                default:
                    $target_page->setXMLContent(
                        $source_page->copyXmlContent(
                            true,
                            $a_target->getId(),
                            $copy_id
                        )
                    );
                    $target_page->buildDom(true);

                    // parse content / blocks

                    if ($direction === "t2p") {
                        $dom = $target_page->getDomDoc();

                        // update profile/consultation hours user id
                        self::updateDomNodes($dom, "//PageContent/Profile", "User", $ilUser->getId());
                        self::updateDomNodes($dom, "//PageContent/ConsultationHours", "User", $ilUser->getId());
                        self::updateDomNodes($dom, "//PageContent/MyCourses", "User", $ilUser->getId());

                        // skills
                        $xpath = new DOMXPath($dom);
                        $nodes = $xpath->query("//PageContent/Skills");
                        foreach ($nodes as $node) {
                            $skill_id = $node->getAttribute("Id");

                            // existing personal skills
                            if (in_array($skill_id, $pskills)) {
                                $node->setAttribute("User", $ilUser->getId());
                            }
                            // new skill
                            elseif ($copy_all || in_array($skill_id, $a_recipe["skills"])) {
                                $skill_personal_service->addPersonalSkill($ilUser->getId(), $skill_id);

                                $node->setAttribute("User", $ilUser->getId());
                            }
                            // remove skill
                            else {
                                $page_element = $node->parentNode;
                                $page_element->parentNode->removeChild($page_element);
                            }
                        }
                    }

                    $valid = true;
                    break;
            }

            if ($valid) {
                // #12038 - update xml from dom
                $target_page->setXMLContent($target_page->getXMLFromDom());

                $target_page->setType($page_type);
                $target_page->setTitle($page_title);
                $target_page->create(false);

                if ($page_type === ilPortfolioPage::TYPE_PAGE) {
                    $target_page->update();	// handle mob usages!
                }
                $page_map[$source_page->getId()] = $target_page->getId();
            }
        }
        ilPortfolioPage::updateInternalLinks($page_map, $a_target);
    }

    protected static function updateDomNodes(
        DOMDocument $a_dom,
        string $a_xpath,
        string $a_attr_id,
        string $a_attr_value
    ): void {
        $xpath_temp = new DOMXPath($a_dom);
        $nodes = $xpath_temp->query($a_xpath);
        foreach ($nodes as $node) {
            $node->setAttribute($a_attr_id, $a_attr_value);
        }
    }

    /**
     * Update internal portfolio links on title change
     */
    public function fixLinksOnTitleChange(array $a_title_changes): void
    {
        foreach (ilPortfolioPage::getAllPortfolioPages($this->getId()) as $port_page) {
            if ($this->getType() === "prtt") {
                $page = new ilPortfolioTemplatePage($port_page["id"]);
            } else {
                $page = new ilPortfolioPage($port_page["id"]);
            }
            if ($page->renameLinksOnTitleChange($a_title_changes)) {
                $page->update(true, true);
            }
        }
    }
}
