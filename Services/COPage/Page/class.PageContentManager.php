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

declare(strict_types=1);

namespace ILIAS\COPage\Page;

use ILIAS\COPage\PC\PCDefinition;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageContentManager
{
    protected \ILIAS\COPage\Link\LinkManager $link;
    protected \ILIAS\COPage\PC\DomainService $pc_service;

    public function __construct(
        \DOMDocument $dom,
        ?PCDefinition $def = null
    ) {
        global $DIC;

        $this->dom = $dom;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->pc_service = $DIC->copage()->internal()->domain()->pc($def);
        $this->link = $DIC->copage()->internal()->domain()->link();
    }

    public function getContentDomNode(string $a_hier_id, string $a_pc_id = ""): ?\DOMNode
    {
        if ($a_hier_id == "pg") {
            $path = "//PageObject";
        } else {
            // get per pc id
            if ($a_pc_id != "") {
                $path = "//*[@PCID = '$a_pc_id']";
            } else {
                // fall back to hier id
                $path = "//*[@HierId = '$a_hier_id']";
            }
        }
        $nodes = $this->dom_util->path($this->dom, $path);
        if (count($nodes) === 1) {
            $cont_node = $nodes->item(0);
            return $cont_node;
        }
        return null;
    }

    public function deleteContent(
        string $a_hid,
        string $a_pcid = "",
        bool $move_operation = false
    ) {
        $curr_node = $this->getContentDomNode($a_hid, $a_pcid);
        $this->handleDeleteContent($curr_node, $move_operation);
        $curr_node->parentNode->removeChild($curr_node);
    }

    public function deleteContents(
        array $a_hids,
        bool $a_self_ass = false,
        bool $move_operation = false
    ) {
        foreach ($a_hids as $a_hid) {
            $a_hid = explode(":", $a_hid);
            // @todo 1: hook
            // do not delete question nodes in assessment pages
            if (!$this->checkForTag("Question", $a_hid[0], (string) ($a_hid[1] ?? "")) || $a_self_ass) {
                $curr_node = $this->getContentDomNode((string) $a_hid[0], (string) ($a_hid[1] ?? ""));
                if (is_object($curr_node)) {
                    $parent_node = $curr_node->parentNode;
                    if ($parent_node->nodeName != "TableRow") {
                        $this->handleDeleteContent($curr_node);
                        $curr_node->parentNode->removeChild($curr_node);
                    }
                }
            }
        }
    }

    protected function checkForTag(
        string $a_content_tag,
        string $a_hier_id,
        string $a_pc_id = ""
    ): bool {
        // get per pc id
        if ($a_pc_id != "") {
            $path = "//*[@PCID = '$a_pc_id']//" . $a_content_tag;
        } else {
            $path = "//*[@HierId = '$a_hier_id']//" . $a_content_tag;
        }

        $nodes = $this->dom_util->path($this->dom, $path);
        if (count($nodes) > 0) {
            return true;
        }
        return false;
    }


    public function handleDeleteContent($a_node = null, $move_operation = false): void
    {
        if (!isset($a_node)) {
            $path = "//PageContent";
            $nodes = $this->dom_util->path($this->dom, $path);
        } else {
            $nodes = array($a_node);
        }

        foreach ($nodes as $node) {
            if ($node instanceof php4DOMNode) {
                $node = $node->myDOMNode;
            }
            /** @var DOMElement $node */
            if ($node->firstChild->nodeName == 'Plugged') {
                \ilPCPlugged::handleDeletedPluggedNode($this, $node->firstChild, $move_operation);
            }
        }
    }

    public function switchEnableMultiple(
        \ilPageObject $page,
        array $a_hids,
        bool $a_self_ass = false
    ) {
        foreach ($a_hids as $a_hid) {
            $a_hid = explode(":", $a_hid);
            $curr_node = $this->getContentDomNode($a_hid[0], $a_hid[1] ?? "");
            if (is_object($curr_node)) {
                if ($curr_node->nodeName == "PageContent") {
                    $cont_obj = $this->pc_service->getByNode($curr_node, $page);
                    if ($cont_obj->isEnabled()) {
                        // do not deactivate question nodes in assessment pages
                        if (!$this->checkForTag("Question", $a_hid[0], (string) ($a_hid[1] ?? "")) || $a_self_ass) {
                            $cont_obj->disable();
                        }
                    } else {
                        $cont_obj->enable();
                    }
                }
            }
        }
    }

    public function setInitialOpenedContent(
        string $a_type = "",
        int $a_id = 0,
        string $a_target = ""
    ): void {
        $il_node = null;
        $link_type = "";
        switch ($a_type) {
            case "media":
                $link_type = "MediaObject";
                $a_id = "il__mob_" . $a_id;
                break;

            case "page":
                $link_type = "PageObject";
                $a_id = "il__pg_" . $a_id;
                break;

            case "term":
                $link_type = "GlossaryItem";
                $a_id = "il__git_" . $a_id;
                $a_target = "Glossary";
                break;
        }

        $path = "//PageObject/InitOpenedContent";
        $nodes = $this->dom_util->path($this->dom, $path);
        if ($link_type == "" || $a_id == "") {
            if (count($nodes) > 0) {
                $c = $nodes->item(0);
                $c->parentNode->removeChild($c);
            }
        } else {
            if (count($nodes) > 0) {
                $init_node = $nodes->item(0);
                foreach ($init_node->childNodes as $child) {
                    if ($child->nodeName === "IntLink") {
                        $il_node = $child;
                    }
                }
            } else {
                $path = "//PageObject";
                $nodes = $this->dom_util->path($this->dom, $path);
                $page_node = $nodes->item(0);
                $init_node = $this->dom->createElement("InitOpenedContent");
                $init_node = $page_node->appendChild($init_node);
                $il_node = $this->dom->createElement("IntLink");
                $il_node = $init_node->appendChild($il_node);
            }
            $il_node->setAttribute("Target", $a_id);
            $il_node->setAttribute("Type", $link_type);
            $il_node->setAttribute("TargetFrame", $a_target);
        }
    }

    /**
     * Get initial opened content
     */
    public function getInitialOpenedContent(): array
    {
        $type = "";
        $path = "//PageObject/InitOpenedContent";
        $il_node = null;
        $nodes = $this->dom_util->path($this->dom, $path);
        if (count($nodes) > 0) {
            $init_node = $nodes->item(0);
            foreach ($init_node->childNodes as $child) {
                if ($child->nodeName == "IntLink") {
                    $il_node = $child;
                }
            }
        }
        if (!is_null($il_node)) {
            $id = $il_node->getAttribute("Target");
            $link_type = $il_node->getAttribute("Type");
            $target = $il_node->getAttribute("TargetFrame");

            switch ($link_type) {
                case "MediaObject":
                    $type = "media";
                    break;

                case "PageObject":
                    $type = "page";
                    break;

                case "GlossaryItem":
                    $type = "term";
                    break;
            }
            $id = \ilInternalLink::_extractObjIdOfTarget($id);
            return array("id" => $id, "type" => $type, "target" => $target);
        }

        return array();
    }


    public function downloadFile(
        \ilPageObject $page,
        string $file_link_id
    ): void {
        $file_id = 0;

        $page->buildDom();
        $dom = $page->getDomDoc();

        if ($this->link->containsFileLinkId($dom, $file_link_id)) {
            $file_id = $this->link->extractFileFromLinkId($file_link_id);
        }
        if (in_array($file_link_id, $this->pc_service->fileList()->getAllFileObjIds($dom))) {
            $file_id = $this->link->extractFileFromLinkId($file_link_id);
        }

        $pcs = \ilPageContentUsage::getUsagesOfPage($page->getId(), $page->getParentType() . ":pg", 0, false);
        foreach ($pcs as $pc) {
            $files = \ilObjFile::_getFilesOfObject("mep:pg", $pc["id"], 0);
            $c_file = $this->link->extractFileFromLinkId($file_link_id);
            if (in_array($c_file, $files)) {
                $file_id = $c_file;
            }
        }

        if ($file_id > 0) {
            $fileObj = new \ilObjFile($file_id, false);
            $fileObj->sendFile();
            exit;
        }
    }

    //
    // Specific methods, should go to other components
    //
}
