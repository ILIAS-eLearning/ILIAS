<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCContentInclude
*
* Content include object (see ILIAS DTD). Inserts content snippets from other
* source (e.g. media pool)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCContentInclude extends ilPageContent
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    public $incl_node;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->setType("incl");
        $this->access = $DIC->access();
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->incl_node = $a_node->first_child();		// this is the snippet node
    }

    /**
    * Create content include node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->incl_node = $this->dom->create_element("ContentInclude");
        $this->incl_node = $this->node->append_child($this->incl_node);
    }

    /**
     * Set content id
     */
    public function setContentId($a_id)
    {
        $this->setContentIncludeAttribute("ContentId", $a_id);
    }
    
    /**
     * Get content id
     */
    public function getContentId()
    {
        return $this->getContentIncludeAttribute("ContentId");
    }

    /**
     * Set content type
     */
    public function setContentType($a_type)
    {
        $this->setContentIncludeAttribute("ContentType", $a_type);
    }
    
    /**
     * Get content type
     */
    public function getContentType()
    {
        return $this->getContentIncludeAttribute("ContentType");
    }

    /**
     * Set installation id
     */
    public function setInstId($a_id)
    {
        $this->setContentIncludeAttribute("InstId", $a_id);
    }

    /**
     * Get installation id
     */
    public function getInstId()
    {
        return $this->getContentIncludeAttribute("InstId");
    }
    
    /**
    * Set attribute of content include tag
    *
    * @param	string		attribute name
    * @param	string		attribute value
    */
    protected function setContentIncludeAttribute($a_attr, $a_value)
    {
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
    *
    * @return	string		attribute name
    */
    public function getContentIncludeAttribute($a_attr)
    {
        if (is_object($this->incl_node)) {
            return  $this->incl_node->get_attribute($a_attr);
        }
    }

    /**
     * After page has been updated (or created)
     *
     * @param object $a_page page object
     * @param DOMDocument $a_domdoc dom document
     * @param string $a_xml xml
     * @param bool $a_creation true on creation, otherwise false
     */
    public static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
    {
        // pc content include
        self::saveContentIncludeUsage($a_page, $a_domdoc);
    }
    
    /**
     * Before page is being deleted
     *
     * @param object $a_page page object
     */
    public static function beforePageDelete($a_page)
    {
        include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
        ilPageContentUsage::deleteAllUsages("incl", $a_page->getParentType() . ":pg", $a_page->getId(), false, $a_page->getLanguage());
    }

    /**
     * After page history entry has been created
     *
     * @param object $a_page page object
     * @param DOMDocument $a_old_domdoc old dom document
     * @param string $a_old_xml old xml
     * @param integer $a_old_nr history number
     */
    public static function afterPageHistoryEntry($a_page, DOMDocument $a_old_domdoc, $a_old_xml, $a_old_nr)
    {
        self::saveContentIncludeUsage($a_page, $a_old_domdoc, $a_old_nr);
    }

    /**
     * save content include usages
     */
    public static function saveContentIncludeUsage($a_page, $a_domdoc, $a_old_nr = 0)
    {
        include_once("./Services/COPage/classes/class.ilPageContentUsage.php");
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
    public static function collectContentIncludes($a_page, $a_domdoc)
    {
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

    /**
     * @inheritdoc
     */
    public function modifyPageContentPostXsl($a_html, $a_mode)
    {
        $lng = $this->lng;

        $end = 0;
        $start = strpos($a_html, "{{{{{ContentInclude;");
        if (is_int($start)) {
            $end = strpos($a_html, "}}}}}", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_html, $start + 20, $end - $start - 20);
            $param = explode(";", $param);

            if ($param[0] == "mep" && is_numeric($param[1])) {
                include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");
                $html = "";
                $snippet_lang = $this->getPage()->getLanguage();
                if (!ilPageObject::_exists("mep", $param[1], $snippet_lang)) {
                    $snippet_lang = "-";
                }
                if (($param[2] <= 0 || $param[2] == IL_INST_ID) && ilPageObject::_exists("mep", $param[1])) {
                    $page_gui = new ilMediaPoolPageGUI($param[1], 0, true, $snippet_lang);
                    if ($a_mode != "offline") {
                        $page_gui->setFileDownloadLink($this->getFileDownloadLink());
                        $page_gui->setFullscreenLink($this->getFullscreenLink());
                        $page_gui->setSourcecodeDownloadScript($this->getSourcecodeDownloadScript());
                    } else {
                        $page_gui->setOutputMode(IL_PAGE_OFFLINE);
                    }

                    $html = $page_gui->getRawContent();
                    if ($a_mode == "edit") {
                        include_once("./Services/Link/classes/class.ilLink.php");
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
                $h2 = substr($a_html, 0, $start) .
                    $html .
                    substr($a_html, $end + 5);
                $a_html = $h2;
                $i++;
            }

            $start = strpos($a_html, "{{{{{ContentInclude;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "}}}}}", $start);
            }
        }
        return $a_html;
    }
}
