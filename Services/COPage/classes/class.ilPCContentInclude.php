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
 * Class ilPCContentInclude
 *
 * Content include object (see ILIAS DTD). Inserts content snippets from other
 * source (e.g. media pool)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCContentInclude extends ilPageContent
{
    protected ilLanguage $lng;
    public php4DOMElement $incl_node;
    protected ilAccessHandler$access;

    /**
    * Init page content component.
    */
    public function init() : void
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("incl");
        $this->access = $DIC->access();
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->incl_node = $a_node->first_child();		// this is the snippet node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->incl_node = $this->dom->create_element("ContentInclude");
        $this->incl_node = $this->node->append_child($this->incl_node);
    }

    /**
     * Set content id
     */
    public function setContentId(int $a_id) : void
    {
        $this->setContentIncludeAttribute("ContentId", (string) $a_id);
    }
    
    /**
     * Get content id
     */
    public function getContentId() : int
    {
        return (int) $this->getContentIncludeAttribute("ContentId");
    }

    /**
     * Set content type
     */
    public function setContentType(string $a_type) : void
    {
        $this->setContentIncludeAttribute("ContentType", $a_type);
    }
    
    /**
     * Get content type
     */
    public function getContentType() : string
    {
        return $this->getContentIncludeAttribute("ContentType");
    }

    /**
     * Set installation id
     */
    public function setInstId(string $a_id) : void
    {
        $this->setContentIncludeAttribute("InstId", $a_id);
    }

    /**
     * Get installation id
     */
    public function getInstId() : string
    {
        return $this->getContentIncludeAttribute("InstId");
    }
    
    /**
     * Set attribute of content include tag
     */
    protected function setContentIncludeAttribute(
        string $a_attr,
        string $a_value
    ) : void {
        if (!empty($a_value)) {
            $this->incl_node->set_attribute($a_attr, $a_value);
        } else {
            if ($this->incl_node->has_attribute($a_attr)) {
                $this->incl_node->remove_attribute($a_attr);
            }
        }
    }

    /**
     * Get content include tag attribute
     */
    public function getContentIncludeAttribute(string $a_attr) : string
    {
        if (is_object($this->incl_node)) {
            return  $this->incl_node->get_attribute($a_attr);
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
        // pc content include
        self::saveContentIncludeUsage($a_page, $a_domdoc);
    }
    
    /**
     * Before page is being deleted
     */
    public static function beforePageDelete(ilPageObject $a_page) : void
    {
        ilPageContentUsage::deleteAllUsages("incl", $a_page->getParentType() . ":pg", $a_page->getId(), false, $a_page->getLanguage());
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
        self::saveContentIncludeUsage($a_page, $a_old_domdoc, $a_old_nr);
    }

    /**
     * save content include usages
     */
    public static function saveContentIncludeUsage(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        int $a_old_nr = 0
    ) : void {
        $ci_ids = self::collectContentIncludes($a_page, $a_domdoc);
        ilPageContentUsage::deleteAllUsages("incl", $a_page->getParentType() . ":pg", $a_page->getId(), $a_old_nr, $a_page->getLanguage());
        foreach ($ci_ids as $ci_id) {
            if ((int) $ci_id["inst_id"] <= 0 || $ci_id["inst_id"] == IL_INST_ID) {
                ilPageContentUsage::saveUsage(
                    "incl",
                    $ci_id["id"],
                    $a_page->getParentType() . ":pg",
                    $a_page->getId(),
                    $a_old_nr,
                    $a_page->getLanguage()
                );
            }
        }
    }

    /**
     * get all content includes that are used within the page
     */
    public static function collectContentIncludes(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ) : array {
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//ContentInclude');

        $ci_ids = array();
        foreach ($nodes as $node) {
            $type = $node->getAttribute("ContentType");
            $id = $node->getAttribute("ContentId");
            $inst_id = $node->getAttribute("InstId");
            $ci_ids[$type . ":" . $id . ":" . $inst_id] = array(
                "type" => $type, "id" => $id, "inst_id" => $inst_id);
        }

        return $ci_ids;
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        $lng = $this->lng;

        $end = 0;
        $start = strpos($a_output, "{{{{{ContentInclude;");
        if (is_int($start)) {
            $end = strpos($a_output, "}}}}}", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_output, $start + 20, $end - $start - 20);
            $param = explode(";", $param);

            if ($param[0] == "mep" && is_numeric($param[1])) {
                $html = "";
                $snippet_lang = $this->getPage()->getLanguage();
                if (!ilPageObject::_exists("mep", $param[1], $snippet_lang)) {
                    $snippet_lang = "-";
                }
                if (($param[2] <= 0 || $param[2] == IL_INST_ID) && ilPageObject::_exists("mep", $param[1])) {
                    $page_gui = new ilMediaPoolPageGUI($param[1], 0, true, $snippet_lang);
                    if ($a_mode != "offline") {
                        $page_gui->setFileDownloadLink($this->getFileDownloadLink());
                        $page_gui->setFullscreenLink($this->getFullscreenLink() . "&pg_type=mep");
                        $page_gui->setSourcecodeDownloadScript($this->getSourcecodeDownloadScript());
                    } else {
                        $page_gui->setOutputMode(ilPageObjectGUI::OFFLINE);
                    }

                    $html = $page_gui->getRawContent();
                    if ($a_mode == "edit") {
                        $par_id = $page_gui->getPageObject()->getParentId();
                        $info = "";
                        foreach (ilObject::_getAllReferences($par_id) as $ref_id) {
                            if ($this->access->checkAccess("write", "", $ref_id)) {
                                $info = " " . $lng->txt("title") . ": " . ilMediaPoolItem::lookupTitle($page_gui->getPageObject()->getId()) .
                                    ", " . $lng->txt("obj_mep") . ": <a href='" . ilLink::_getLink($ref_id) . "'>" . ilObject::_lookupTitle($par_id) . "</a>";
                            }
                        }
                        $html = '<p class="small light">' . $lng->txt("copg_snippet_cannot_be_edited") . $info . '</p>' . $html;
                    }
                } else {
                    if ($a_mode == "edit") {
                        if ($param[2] <= 0) {
                            $html = "// " . $lng->txt("cont_missing_snippet") . " //";
                        } else {
                            $html = "// " . $lng->txt("cont_snippet_from_another_installation") . " //";
                        }
                    }
                }
                $h2 = substr($a_output, 0, $start) .
                    $html .
                    substr($a_output, $end + 5);
                $a_output = $h2;
                $i++;
            }

            $start = strpos($a_output, "{{{{{ContentInclude;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "}}}}}", $start);
            }
        }
        return $a_output;
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
            "incl",
            $parent_type . ":pg",
            $page_id,
            $delete_lower_than_nr,
            $lang
        );
    }
}
