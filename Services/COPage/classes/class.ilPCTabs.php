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
 * Tabbed contents (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTabs extends ilPageContent
{
    public const ACCORDION_HOR = "HorizontalAccordion";
    public const ACCORDION_VER = "VerticalAccordion";
    public const CAROUSEL = "Carousel";

    public function init(): void
    {
        $this->setType("tabs");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "Tabs");
    }

    protected function setTabsAttribute(
        string $a_attr,
        string $a_value
    ): void {
        $this->dom_util->setAttribute($this->getChildNode(), $a_attr, $a_value);
    }

    /**
     * @param	string		$a_type		("HorizontalTabs" | "Accordion")
     */
    public function setTabType(
        string $a_type = "HorizontalTabs"
    ): void {
        switch ($a_type) {
            case ilPCTabs::ACCORDION_VER:
            case ilPCTabs::ACCORDION_HOR:
            case ilPCTabs::CAROUSEL:
                $this->setTabsAttribute("Type", $a_type);
                break;
        }
    }

    public function getTabType(): string
    {
        return $this->getChildNode()->getAttribute("Type");
    }

    public function setContentWidth(string $a_val): void
    {
        $this->setTabsAttribute("ContentWidth", $a_val);
    }

    public function getContentWidth(): string
    {
        return $this->getChildNode()->getAttribute("ContentWidth");
    }

    public function setContentHeight(string $a_val): void
    {
        $this->setTabsAttribute("ContentHeight", $a_val);
    }

    public function getContentHeight(): string
    {
        return $this->getChildNode()->getAttribute("ContentHeight");
    }

    public function setHorizontalAlign(string $a_val): void
    {
        $this->setTabsAttribute("HorizontalAlign", $a_val);
    }

    public function getHorizontalAlign(): string
    {
        return $this->getChildNode()->getAttribute("HorizontalAlign");
    }

    public function setBehavior(string $a_val): void
    {
        $this->setTabsAttribute("Behavior", $a_val);
    }

    public function getBehavior(): string
    {
        return $this->getChildNode()->getAttribute("Behavior");
    }

    public function getCaptions(): array
    {
        $captions = array();
        $k = 0;
        foreach ($this->getChildNode()->childNodes as $child) {
            if ($child->nodeName == "Tab") {
                $pc_id = $child->getAttribute("PCID");
                $hier_id = $child->getAttribute("HierId");
                $current_caption = "";
                foreach ($child->childNodes as $tab_child) {
                    if ($tab_child->nodeName == "TabCaption") {
                        $current_caption = $this->dom_util->getContent($tab_child);
                    }
                }
                $captions[] = array("pos" => $k,
                    "caption" => $current_caption, "pc_id" => $pc_id, "hier_id" => $hier_id);
                $k++;
            }
        }

        return $captions;
    }

    public function getCaption(
        string $a_hier_id,
        string $a_pc_id
    ): string {
        foreach ($this->getCaptions() as $cap) {
            if ($cap["pc_id"] === $a_pc_id && $cap["hier_id"] === $a_hier_id) {
                return $cap["caption"];
            }
        }
        return "";
    }

    /**
     * Save positions of tabs
     */
    public function savePositions(
        array $a_pos
    ): void {
        asort($a_pos);

        // File Item
        $nodes = array();
        foreach ($this->getChildNode()->childNodes as $child) {
            if ($child->nodeName == "Tab") {
                $pc_id = $child->getAttribute("PCID");
                $hier_id = $child->getAttribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $child;
            }
        }
        $this->dom_util->deleteAllChildsByName($this->getChildNode(), ["Tab"]);

        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->getChildNode()->appendChild($nodes[$k]);
            }
        }
    }

    public function saveCaptions(array $a_captions): void
    {
        // iterate all tab nodes
        foreach ($this->getChildNode()->childNodes as $child) {
            if ($child->nodeName == "Tab") {
                $pc_id = $child->getAttribute("PCID");
                $hier_id = $child->getAttribute("HierId");
                $k = $hier_id . ":" . $pc_id;
                // if caption given, set it, otherwise delete caption subitem
                if ($a_captions[$k] != "") {
                    $this->dom_util->setFirstOptionalElement(
                        $child,
                        "TabCaption",
                        array(),
                        $a_captions[$k],
                        array()
                    );
                } else {
                    $this->dom_util->deleteAllChildsByName($child, array("TabCaption"));
                }
            }
        }
    }

    public function deleteTab(
        string $a_hier_id,
        string $a_pc_id
    ): void {
        // File Item
        foreach ($this->getChildNode()->childNodes as $child) {
            if ($child->nodeName == "Tab") {
                if ($a_pc_id == $child->getAttribute("PCID") &&
                    $a_hier_id == $child->getAttribute("HierId")) {
                    $child->parentNode->removeChild($child);
                }
            }
        }
    }

    public function addTab(string $a_caption): void
    {
        $new_item = $this->dom_doc->createElement("Tab");
        $new_item = $this->getChildNode()->appendChild($new_item);
        $this->dom_util->setFirstOptionalElement(
            $new_item,
            "TabCaption",
            array(),
            $a_caption,
            array()
        );
    }

    public function setTemplate(string $a_template): void
    {
        $this->setTabsAttribute("Template", $a_template);
    }

    public function getTemplate(): string
    {
        return $this->getChildNode()->getAttribute("Template");
    }

    public static function getLangVars(): array
    {
        return array("pc_vacc", "pc_hacc", "pc_carousel");
    }

    public function setAutoTime(?int $a_val): void
    {
        $this->setTabsAttribute("AutoAnimWait", (string) $a_val);
    }

    public function getAutoTime(): ?int
    {
        $val = $this->getChildNode()->getAttribute("AutoAnimWait");
        if ($val) {
            return (int) $val;
        }
        return null;
    }

    public function setRandomStart(bool $a_val): void
    {
        $this->setTabsAttribute("RandomStart", $a_val);
    }

    public function getRandomStart(): bool
    {
        return (bool) $this->getChildNode()->getAttribute("RandomStart");
    }

    public function getJavascriptFiles(string $a_mode): array
    {
        return ilAccordionGUI::getLocalJavascriptFiles();
    }

    public function getCssFiles(string $a_mode): array
    {
        return ilAccordionGUI::getLocalCssFiles();
    }

    public function saveCaption(string $pc_id, string $caption): void
    {
        $tab_nodes = $this->tabs_node->child_nodes();
        for ($i = 0; $i < count($tab_nodes); $i++) {
            if ($tab_nodes[$i]->node_name() == "Tab") {
                $current_pc_id = $tab_nodes[$i]->get_attribute("PCID");
                if ($current_pc_id === $pc_id) {
                    if ($caption !== "") {
                        ilDOMUtil::setFirstOptionalElement(
                            $this->dom,
                            $tab_nodes[$i],
                            "TabCaption",
                            array(),
                            $caption,
                            array()
                        );
                    } else {
                        ilDOMUtil::deleteAllChildsByName($tab_nodes[$i], array("TabCaption"));
                    }
                }
            }
        }
    }

    public function addAbove(string $pc_id, string $caption = ""): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $new_tab = $tab->parentNode->insertBefore($new_tab, $tab);
            if ($caption !== "") {
                $dom_util = $this->domain->domUtil();
                $dom_util->setFirstOptionalElement(
                    $new_tab,
                    "TabCaption",
                    array(),
                    $caption,
                    array()
                );
            }
        }
    }

    public function addBelow(string $pc_id, string $caption = ""): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            if ($next = $tab->nextSibling) {
                $new_tab = $next->parentNode->insertBefore($new_tab, $next);
            } else {
                $new_tab = $tab->parentNode->appendChild($new_tab);
            }

            if ($caption !== "") {
                $dom_util = $this->domain->domUtil();
                $dom_util->setFirstOptionalElement(
                    $new_tab,
                    "TabCaption",
                    array(),
                    $caption,
                    array()
                );
            }
        }
    }

    public function moveUp(string $pc_id): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $prev = $tab->previousSibling;
            if ($prev) {
                $tab->parentNode->removeChild($tab);
                $tab = $prev->parentNode->insertBefore($tab, $prev);
            }
        }
    }

    public function moveDown(string $pc_id): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $next = $tab->nextSibling;
            if ($next) {
                $next2 = $next->nextSibling;
                $tab->parentNode->removeChild($tab);
                if ($next2) {
                    $tab = $next2->parentNode->insertBefore($tab, $next2);
                } else {
                    $tab = $next->parentNode->appendChild($tab);
                }
            }
        }
    }

    public function moveTop(string $pc_id): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $prev = $tab->previousSibling;
            if ($prev) {
                $tab->parentNode->removeChild($tab);
                $first = $prev->parentNode->childNodes->item(0);
                $tab = $prev->parentNode->insertBefore($tab, $first);
            }
        }
    }

    public function moveBottom(string $pc_id): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $next = $tab->nextSibling;
            if ($next) {
                $tab->parentNode->removeChild($tab);
                $tab = $next->parentNode->appendChild($tab);
            }
        }
    }

    public function deletePanel(string $pc_id): void
    {
        $dom = $this->getPage()->getDomDoc();
        $new_tab = $dom->createElement("Tab");
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $tab->parentNode->removeChild($tab);
        }
    }

    public function getNodeXml(string $pc_id): string
    {
        $tab = $this->getPage()->getDomNodeForPCId($pc_id);
        if (!is_null($tab)) {
            $xml = "";
            foreach ($tab->childNodes as $node) {
                if ($node->nodeName === "PageContent") {
                    $xml .= $node->ownerDocument->saveXml($node);
                }
            }
            return $xml;
        }
        return "";
    }

}
