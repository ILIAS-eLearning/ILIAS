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
 * Class ilPCSection
 * Section content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCSection extends ilPageContent
{
    protected ilAccessHandler $access;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    public php4DOMElement $sec_node;

    public function init(): void
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setType("sec");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->sec_node = $a_node->first_child();		// this is the Section node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->sec_node = $this->dom->create_element("Section");
        $this->sec_node = $this->node->append_child($this->sec_node);
        $this->sec_node->set_attribute("Characteristic", "Block");
    }

    public function setCharacteristic(string $a_char): void
    {
        if (!empty($a_char)) {
            $this->sec_node->set_attribute("Characteristic", $a_char);
        } else {
            if ($this->sec_node->has_attribute("Characteristic")) {
                $this->sec_node->remove_attribute("Characteristic");
            }
        }
    }

    public function getCharacteristic(): string
    {
        if (is_object($this->sec_node)) {
            $char = $this->sec_node->get_attribute("Characteristic");
            if (substr($char, 0, 4) == "ilc_") {
                $char = substr($char, 4);
            }
            return $char;
        }
        return "";
    }

    public static function getLangVars(): array
    {
        return array("ed_insert_section");
    }

    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ): void {
        self::saveTimings($a_page);
    }

    /**
     * @throws ilDateTimeException
     */
    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $a_output = self::insertTimings($a_output);
        $a_output = $this->handleAccess($a_output, $a_mode);

        return $a_output;
    }

    public function setActiveFrom(int $a_unix_ts): void
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
     */
    public function getActiveFrom(): int
    {
        if (is_object($this->sec_node)) {
            return (int) $this->sec_node->get_attribute("ActiveFrom");
        }
        return 0;
    }

    /**
     * Set activation to
     */
    public function setActiveTo(int $a_unix_ts): void
    {
        if ($a_unix_ts > 0) {
            $this->sec_node->set_attribute("ActiveTo", $a_unix_ts);
        } else {
            if ($this->sec_node->has_attribute("ActiveTo")) {
                $this->sec_node->remove_attribute("ActiveTo");
            }
        }
    }

    public function getActiveTo(): int
    {
        if (is_object($this->sec_node)) {
            return (int) $this->sec_node->get_attribute("ActiveTo");
        }
        return 0;
    }

    protected function setAttribute(
        string $a_attr,
        string $a_val
    ): void {
        if (!empty($a_val)) {
            $this->sec_node->set_attribute($a_attr, $a_val);
        } else {
            if ($this->sec_node->has_attribute($a_attr)) {
                $this->sec_node->remove_attribute($a_attr);
            }
        }
    }

    public function getAttribute(string $a_attr): string
    {
        if (is_object($this->sec_node)) {
            return $this->sec_node->get_attribute($a_attr);
        }
        return "";
    }

    /**
     * Set permission
     * @param string $a_val "read"|"write"|"visible"|"no_read"
     */
    public function setPermission(string $a_val): void
    {
        $this->setAttribute("Permission", $a_val);
    }

    public function getPermission(): string
    {
        return $this->getAttribute("Permission");
    }

    public function setPermissionRefId(int $a_ref_id): void
    {
        $this->setAttribute("PermissionRefId", "il__ref_" . $a_ref_id);
    }

    public function getPermissionRefId(): int
    {
        $id = explode("_", $this->getAttribute("PermissionRefId"));
        if (in_array($id[1], array("", 0, IL_INST_ID))) {
            return (int) $id[3];
        }
        return 0;
    }

    /**
     * Set no link
     */
    public function setNoLink(): void
    {
        ilDOMUtil::deleteAllChildsByName($this->sec_node, array("IntLink", "ExtLink"));
    }

    /**
     * Set link of area to an external one
     */
    public function setExtLink(string $a_href): void
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
    public function setIntLink(
        string $a_type,
        string $a_target,
        string $a_target_frame
    ): void {
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

    public function getLink(): array
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


    public function handleAccess(
        string $a_html,
        string $a_mode
    ): string {
        $ilAccess = $this->access;

        while (($start = strpos($a_html, "{{{{{Section;Access;")) > 0) {
            $end = strpos($a_html, "}}}}}", $start);
            $access_attr = explode(";", substr($a_html, $start, $end - $start));
            $id = explode("_", $access_attr[3]);
            $section_nr = $access_attr[6];
            $access = true;
            if (in_array($id[1], array("", 0, IL_INST_ID)) && $id[3] > 0) {
                if ($access_attr[5] == "no_read") {
                    $access = !$ilAccess->checkAccess("read", "", $id[3]);
                } else {
                    $access = $ilAccess->checkAccess($access_attr[5], "", $id[3]);
                }
            }
            if ($a_mode == ilPageObjectGUI::EDIT) {
                $access = true;
            }
            $end_limiter = "{{{{{Section;AccessEnd;" . $section_nr . "}}}}}";
            if ($access) {
                $a_html = substr($a_html, 0, $start) . substr($a_html, $end + 5);
                $a_html = str_replace($end_limiter, "", $a_html);
            } else {
                $end = strpos($a_html, $end_limiter, $start);
                $a_html = substr($a_html, 0, $start) . substr($a_html, $end + strlen($end_limiter));
            }
        }

        $a_html = str_replace("{{{{{Section;Access}}}}}", "", $a_html);
        return $a_html;
    }

    public static function saveTimings(
        ilPageObject $a_page
    ): void {
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
        for ($i = 0; $i < count($res->nodeset); $i++) {
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
     * @return string trigger string
     * @throws ilDateTimeException
     */
    public static function getCacheTriggerString(
        ilPageObject $a_page
    ): string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT * FROM copg_section_timings " .
            " WHERE page_id = " . $ilDB->quote($a_page->getId(), "integer") .
            " AND parent_type = " . $ilDB->quote($a_page->getParentType(), "text")
        );
        $str = "1";     // changed to 1 to force cache miss for #24277
        $current_ts = new ilDateTime(time(), IL_CAL_UNIX);
        $current_ts = $current_ts->get(IL_CAL_UNIX);
        while ($rec = $ilDB->fetchAssoc($set)) {
            $unix_ts = $rec["unix_ts"];
            if ($unix_ts < $current_ts) {
                $unix_ts .= "a";
            }
            $str .= "-" . $unix_ts;
        }

        return $str;
    }

    /**
     * Insert timings (in edit mode)
     * @throws ilDateTimeException
     */
    public function insertTimings(
        string $a_html
    ): string {
        $lng = $this->lng;

        $end = 0;
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
                $html .= $lng->txt("cont_active_from") . ": " . ilDatePresentation::formatDate($from);
            }
            if ($to != "") {
                $to = new ilDateTime($to, IL_CAL_UNIX);
                $html .= " " . $lng->txt("cont_active_to") . ": " . ilDatePresentation::formatDate($to);
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

    public function getProtected(): bool
    {
        if (is_object($this->sec_node)) {
            return ($this->sec_node->get_attribute("Protected") == "1");
        }

        return false;
    }

    public function setProtected(bool $val): void
    {
        if ($val) {
            $this->sec_node->set_attribute("Protected", "1");
        } else {
            $this->sec_node->set_attribute("Protected", "0");
        }
    }

    public function getModel(): ?stdClass
    {
        if ($this->sec_node->node_name() != "Section") {
            return null;
        }
        $model = new stdClass();
        $model->protected = $this->getProtected();

        return $model;
    }
}
