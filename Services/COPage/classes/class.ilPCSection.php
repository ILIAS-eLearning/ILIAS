<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCSection
*
* Section content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCSection extends ilPageContent
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $dom;
    public $sec_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setType("sec");
    }

    /**
    * Set node
    */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->sec_node = $a_node->first_child();		// this is the Section node
    }

    /**
    * Create section node in xml.
    *
    * @param	object	$a_pg_obj		Page Object
    * @param	string	$a_hier_id		Hierarchical ID
    */
    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->sec_node = $this->dom->create_element("Section");
        $this->sec_node = $this->node->append_child($this->sec_node);
        $this->sec_node->set_attribute("Characteristic", "Block");
    }

    /**
    * Set Characteristic of section
    *
    * @param	string	$a_char		Characteristic
    */
    public function setCharacteristic($a_char)
    {
        if (!empty($a_char)) {
            $this->sec_node->set_attribute("Characteristic", $a_char);
        } else {
            if ($this->sec_node->has_attribute("Characteristic")) {
                $this->sec_node->remove_attribute("Characteristic");
            }
        }
    }

    /**
    * Get characteristic of section.
    *
    * @return	string		characteristic
    */
    public function getCharacteristic()
    {
        if (is_object($this->sec_node)) {
            $char =  $this->sec_node->get_attribute("Characteristic");
            if (substr($char, 0, 4) == "ilc_") {
                $char = substr($char, 4);
            }
            return $char;
        }
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_section");
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
        include_once("./Services/COPage/classes/class.ilPCSection.php");
        self::saveTimings($a_page);
    }

    /**
     * Modify page content after xsl
     *
     * @param string $a_output
     * @return string
     */
    public function modifyPageContentPostXsl($a_output, $a_mode)
    {
        $a_output = self::insertTimings($a_output);
        $a_output = $this->handleAccess($a_output, $a_mode);

        return $a_output;
    }

    /**
     * Set activation from
     *
     * @param string $a_unix_ts unix ts activation from
     */
    public function setActiveFrom($a_unix_ts)
    {
        if ($a_unix_ts > 0) {
            $this->sec_node->set_attribute("ActiveFrom", $a_unix_ts);
        } else {
            if ($this->sec_node->has_attribute("ActiveFrom")) {
                $this->sec_node->remove_attribute("ActiveFrom");
            }
        }
    }

    /**
     * Get activation from
     *
     * @return string unix ts activation from
     */
    public function getActiveFrom()
    {
        if (is_object($this->sec_node)) {
            return $this->sec_node->get_attribute("ActiveFrom");
        }

        return "";
    }

    /**
     * Set activation to
     *
     * @param string $a_unix_ts unix ts activation to
     */
    public function setActiveTo($a_unix_ts)
    {
        if ($a_unix_ts > 0) {
            $this->sec_node->set_attribute("ActiveTo", $a_unix_ts);
        } else {
            if ($this->sec_node->has_attribute("ActiveTo")) {
                $this->sec_node->remove_attribute("ActiveTo");
            }
        }
    }

    /**
     * Get activation to
     *
     * @return string unix ts activation to
     */
    public function getActiveTo()
    {
        if (is_object($this->sec_node)) {
            return $this->sec_node->get_attribute("ActiveTo");
        }

        return "";
    }

    /**
     * Set attribute
     *
     * @param string $a_attr attribute
     * @param string $a_val attribute value
     */
    protected function setAttribute($a_attr, $a_val)
    {
        if (!empty($a_val)) {
            $this->sec_node->set_attribute($a_attr, $a_val);
        } else {
            if ($this->sec_node->has_attribute($a_attr)) {
                $this->sec_node->remove_attribute($a_attr);
            }
        }
    }

    /**
     * Get attribute
     *
     * @param string $a_attr attribute
     * @return string attribute value
     */
    public function getAttribute($a_attr)
    {
        if (is_object($this->sec_node)) {
            return $this->sec_node->get_attribute($a_attr);
        }
        return "";
    }

    /**
     * Set permission
     *
     * @param string $a_val "read"|"write"|"visible"
     */
    public function setPermission($a_val)
    {
        $this->setAttribute("Permission", $a_val);
    }

    /**
     * Get permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->getAttribute("Permission");
    }


    /**
     * Set permission ref id
     *
     * @param integer $a_ref_id ref id
     */
    public function setPermissionRefId($a_ref_id)
    {
        $this->setAttribute("PermissionRefId", "il__ref_" . $a_ref_id);
    }

    /**
     * Get permission ref id
     *
     * @return int ref id
     */
    public function getPermissionRefId()
    {
        $id = explode("_", $this->getAttribute("PermissionRefId"));
        if (in_array($id[1], array("", 0, IL_INST_ID))) {
            return $id[3];
        }
        return "";
    }

    /**
     * Set no link
     */
    public function setNoLink()
    {
        ilDOMUtil::deleteAllChildsByName($this->sec_node, array("IntLink", "ExtLink"));
    }

    /**
     * Set link of area to an external one
     * @param string $a_href
     */
    public function setExtLink($a_href)
    {
        $this->setNoLink();
        if (trim($a_href) != "") {
            $attributes = array("Href" => trim($a_href));
            ilDOMUtil::setFirstOptionalElement(
                $this->dom,
                $this->sec_node,
                "ExtLink",
                array(""),
                "",
                $attributes
            );
        }
    }

    /**
     * Set link of area to an internal one
     */
    public function setIntLink($a_type, $a_target, $a_target_frame)
    {
        $this->setNoLink();
        $attributes = array("Type" => $a_type, "Target" => $a_target,
            "TargetFrame" => $a_target_frame);
        ilDOMUtil::setFirstOptionalElement(
            $this->dom,
            $this->sec_node,
            "IntLink",
            array(""),
            "",
            $attributes
        );
    }

    /**
     * Get link
     *
     * @param
     * @return
     */
    public function getLink()
    {
        $childs = $this->sec_node->child_nodes();
        foreach ($childs as $child) {
            if ($child->node_name() == "ExtLink") {
                return array("LinkType" => "ExtLink",
                    "Href" => $child->get_attribute("Href"));
            }
            if ($child->node_name() == "IntLink") {
                return array("LinkType" => "IntLink",
                    "Target" => $child->get_attribute("Target"),
                    "Type" => $child->get_attribute("Type"),
                    "TargetFrame" => $child->get_attribute("TargetFrame"));
            }
        }
        return array("LinkType" => "NoLink");
    }


    /**
     * @param $a_html
     * @param $a_mode
     * @return mixed|string
     */
    public function handleAccess($a_html, $a_mode)
    {
        $ilAccess = $this->access;

        while (($start = strpos($a_html, "{{{{{Section;Access;")) > 0) {
            $end = strpos($a_html, "}}}}}", $start);
            $access_attr = explode(";", substr($a_html, $start, $end - $start));
            $id = explode("_", $access_attr[3]);
            $access = true;
            if (in_array($id[1], array("", 0, IL_INST_ID)) && $id[3] > 0) {
                $access = $ilAccess->checkAccess($access_attr[5], "", $id[3]);
            }
            if ($access) {
                $a_html = substr($a_html, 0, $start) . substr($a_html, $end + 5);
            } else {
                $end = strpos($a_html, "{{{{{Section;Access}}}}}", $start);
                $a_html = substr($a_html, 0, $start) . substr($a_html, $end + 24);
            }
        }

        $a_html = str_replace("{{{{{Section;Access}}}}}", "", $a_html);

        return $a_html;
    }

    /**
     * Save timings
     *
     * @param ilPageObject $a_page  page object
     */
    public static function saveTimings($a_page)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate(
            "DELETE FROM copg_section_timings WHERE " .
            " page_id = " . $ilDB->quote($a_page->getId(), "integer") .
            " AND parent_type = " . $ilDB->quote($a_page->getParentType(), "text")
        );

        $xml = $a_page->getXMLFromDom();

        $doc = domxml_open_mem($xml);

        // media aliases
        $xpc = xpath_new_context($doc);
        $path = "//Section";
        $res = xpath_eval($xpc, $path);
        for ($i=0; $i < count($res->nodeset); $i++) {
            $from = $res->nodeset[$i]->get_attribute("ActiveFrom");
            if ($from != "") {
                $ilDB->replace(
                    "copg_section_timings",
                    array(
                        "page_id" => array("integer", $a_page->getId()),
                        "parent_type" => array("text", $a_page->getParentType()),
                        "unix_ts" => array("integer", $from)
                        ),
                    array()
                );
            }
            $to = $res->nodeset[$i]->get_attribute("ActiveTo");
            if ($to != "") {
                $ilDB->replace(
                    "copg_section_timings",
                    array(
                        "page_id" => array("integer", $a_page->getId()),
                        "parent_type" => array("text", $a_page->getParentType()),
                        "unix_ts" => array("integer", $to)
                    ),
                    array()
                );
            }
        }
    }

    /**
     * Get page cache update trigger string
     *
     * @param ilPageObject $a_page
     * @return string trigger string
     */
    public static function getCacheTriggerString($a_page)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM copg_section_timings " .
            " WHERE page_id = " . $ilDB->quote($a_page->getId(), "integer") .
            " AND parent_type = " . $ilDB->quote($a_page->getParentType(), "text")
        );
        $str = "";
        $current_ts = new ilDateTime(time(), IL_CAL_UNIX);
        $current_ts = $current_ts->get(IL_CAL_UNIX);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $unix_ts = $rec["unix_ts"];
            if ($unix_ts < $current_ts) {
                $unix_ts.= "a";
            }
            $str.= "-" . $unix_ts;
        }

        return $str;
    }

    /**
     * Insert timings (in edit mode)
     *
     * @param string $a_html html
     * @return string htmls
     */
    public function insertTimings($a_html)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $c_pos = 0;
        $start = strpos($a_html, "{{{{{Section;ActiveFrom");
        if (is_int($start)) {
            $end = strpos($a_html, "}}}}}", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_html, $start + 13, $end - $start - 13);
            $param = explode(";", $param);
            $from = $param[1];
            $to = $param[3];
            $html = "";
            if ($from != "") {
                ilDatePresentation::setUseRelativeDates(false);
                $from = new ilDateTime($from, IL_CAL_UNIX);
                $html.= $lng->txt("cont_active_from") . ": " . ilDatePresentation::formatDate($from);
            }
            if ($to != "") {
                $to = new ilDateTime($to, IL_CAL_UNIX);
                $html.= " " . $lng->txt("cont_active_to") . ": " . ilDatePresentation::formatDate($to);
            }

            $h2 = substr($a_html, 0, $start) .
                $html .
                substr($a_html, $end + 5);
            $a_html = $h2;
            $i++;

            $start = strpos($a_html, "{{{{{Section;ActiveFrom;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "}}}}}", $start);
            }
        }
        return $a_html;
    }
}
