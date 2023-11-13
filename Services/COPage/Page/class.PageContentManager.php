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
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;
    protected \DOMDocument $dom;
    protected \ILIAS\COPage\Link\LinkManager $link;
    protected \ILIAS\COPage\PC\DomainService $pc_service;
    protected \DOMNode $page_object_node;

    public function __construct(
        \DOMDocument $dom,
        ?PCDefinition $def = null
    ) {
        global $DIC;

        $this->dom = $dom;
        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->pc_service = $DIC->copage()->internal()->domain()->pc($def);
        $this->link = $DIC->copage()->internal()->domain()->link();

        $path = "//PageObject";
        $nodes = $this->dom_util->path($dom, $path);
        if (count($nodes) == 1) {
            $this->page_object_node = $nodes->item(0);
        }
    }

    protected function getPageObjectNode(): \DOMNode
    {
        return $this->page_object_node;
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
        \ilPageObject $page,
        string $a_hid,
        string $a_pcid = "",
        bool $move_operation = false
    ) {
        $curr_node = $this->getContentDomNode($a_hid, $a_pcid);
        $this->handleDeleteContent($page, $curr_node, $move_operation);
        $curr_node->parentNode->removeChild($curr_node);
    }

    public function deleteContents(
        \ilPageObject $page,
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
                        $this->handleDeleteContent($page, $curr_node, $move_operation);
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


    public function handleDeleteContent(\ilPageObject $page, $a_node = null, $move_operation = false): void
    {
        if (!isset($a_node)) {
            $path = "//PageContent";
            $nodes = $this->dom_util->path($this->dom, $path);
        } else {
            $nodes = array($a_node);
        }

        foreach ($nodes as $node) {
            if ($node->firstChild->nodeName === 'Plugged') {
                \ilPCPlugged::handleDeletedPluggedNode($page, $node->firstChild, $move_operation);
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

    /**
     * inserts installation id into ids (e.g. il__pg_4 -> il_23_pg_4)
     * this is needed for xml export of page
     * @param string $a_inst installation id
     * @param bool $a_res_ref_to_obj_id convert repository links obj_<ref_id> to <type>_<obj_id>
     */
    public function insertInstIntoIDs(
        string $a_inst,
        bool $a_res_ref_to_obj_id = true
    ): void {
        $dom = $this->dom;
        // insert inst id into internal links
        $path = "//IntLink";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $target = $node->getAttribute("Target");
            $type = $node->getAttribute("Type");

            if (substr($target, 0, 4) == "il__") {
                $id = substr($target, 4, strlen($target) - 4);

                // convert repository links obj_<ref_id> to <type>_<obj_id>
                // this leads to bug 6685.
                if ($a_res_ref_to_obj_id && $type == "RepositoryItem") {
                    $id_arr = explode("_", $id);

                    // changed due to bug 6685
                    $ref_id = $id_arr[1];
                    $obj_id = \ilObject::_lookupObjId((int) $id_arr[1]);

                    $otype = \ilObject::_lookupType($obj_id);
                    if ($obj_id > 0) {
                        // changed due to bug 6685
                        // the ref_id should be used, if the content is
                        // imported on the same installation
                        // the obj_id should be used, if a different
                        // installation imports, but has an import_id for
                        // the object id.
                        $id = $otype . "_" . $obj_id . "_" . $ref_id;
                        //$id = $otype."_".$ref_id;
                    }
                }
                $new_target = "il_" . $a_inst . "_" . $id;
                $node->setAttribute("Target", $new_target);
            }
        }

        // insert inst id into media aliases
        $path = "//MediaAlias";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $origin_id = $node->getAttribute("OriginId");
            if (substr($origin_id, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($origin_id, 4, strlen($origin_id) - 4);
                $node->setAttribute("OriginId", $new_id);
            }
        }

        // insert inst id file item identifier entries
        $path = "//FileItem/Identifier";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $origin_id = $node->getAttribute("Entry");
            if (substr($origin_id, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($origin_id, 4, strlen($origin_id) - 4);
                $node->setAttribute("Entry", $new_id);
            }
        }

        // insert inst id into question references
        $path = "//Question";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $qref = $node->getAttribute("QRef");
            //echo "<br>setted:".$qref;
            if (substr($qref, 0, 4) == "il__") {
                $new_id = "il_" . $a_inst . "_" . substr($qref, 4, strlen($qref) - 4);
                //echo "<br>setting:".$new_id;
                $node->setAttribute("QRef", $new_id);
            }
        }

        // insert inst id into content snippets
        $path = "//ContentInclude";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $ci = $node->getAttribute("InstId");
            if ($ci == "") {
                $node->setAttribute("InstId", $a_inst);
            }
        }
    }

    public function pasteContents(
        \ilObjUser $user,
        string $a_hier_id,
        \ilPageObject $page,
        bool $a_self_ass = false
    ) {
        $a_hid = explode(":", $a_hier_id);
        $content = $user->getPCClipboardContent();

        // we insert from last to first, because we insert all at the
        // same hier_id
        for ($i = count($content) - 1; $i >= 0; $i--) {
            $c = $content[$i];
            $error = null;
            $temp_dom = $this->dom_util->docFromString(
                '<?xml version="1.0" encoding="UTF-8"?>' . $c,
                $error
            );
            if (empty($error)) {
                $this->handleCopiedContent($page, $temp_dom, $a_self_ass);
                $path = "//PageContent";
                $nodes = $this->dom_util->path($temp_dom, $path);
                foreach ($nodes as $node) {
                    $new_pc_node = $node;
                    $cloned_pc_node = $new_pc_node->cloneNode(true);
                    $cloned_pc_node = $this->dom->importNode($cloned_pc_node, true);
                    $this->insertContentNode(
                        $cloned_pc_node,
                        $a_hid[0],
                        IL_INSERT_AFTER,
                        $a_hid[1]
                    );
                }
            } else {
                //var_dump($error);
            }
        }
    }

    public function copyXmlContent(
        \ilPageObject $page,
        bool $a_clone_mobs = false,
        int $a_new_parent_id = 0,
        int $obj_copy_id = 0,
        bool $self_ass = true
    ): string {
        $this->handleCopiedContent($page, $this->dom, $self_ass, $a_clone_mobs, $a_new_parent_id, $obj_copy_id);
        $this->dom->documentElement;
        $xml = $this->dom_util->dump($this->dom->documentElement);
        $xml = preg_replace('/<\?xml[^>]*>/i', "", $xml);
        $xml = preg_replace('/<!DOCTYPE[^>]*>/i', "", $xml);
        return $xml;
    }

    /**
     * Handle copied content
     * This function copies items, that must be copied, if page
     * content is duplicated.
     * Currently called by
     * - copyXmlContent
     * - called by pasteContents
     * - called by ilPageEditorGUI->paste -> pasteContents
     */
    protected function handleCopiedContent(
        \ilPageObject $page,
        \DOMDocument $dom,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        $defs = $this->pc_service->definition()->getPCDefinitions();
        foreach ($defs as $def) {
            $cl = $def["pc_class"];
            if ($cl == 'ilPCPlugged') {
                // the page object is provided for ilPageComponentPlugin
                \ilPCPlugged::handleCopiedPluggedContent($page, $dom);
            } else {
                $cl::handleCopiedContent($dom, $a_self_ass, $a_clone_mobs, $new_parent_id, $obj_copy_id);
            }
        }
    }

    public function insertContentNode(
        \DOMNode $a_cont_node,
        string $a_pos,
        int $a_mode = IL_INSERT_AFTER,
        string $a_pcid = ""
    ): void {
        // move mode into container elements is always INSERT_CHILD
        $curr_node = $this->getContentDomNode($a_pos, $a_pcid);
        $curr_name = $curr_node->nodeName;
        // @todo: try to generalize
        if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
            ($curr_name == "ListItem") || ($curr_name == "Section")
            || ($curr_name == "Tab") || ($curr_name == "ContentPopup")
            || ($curr_name == "GridCell")) {
            $a_mode = IL_INSERT_CHILD;
        }

        $hid = $curr_node->getAttribute("HierId");
        if ($hid != "") {
            $a_pos = $hid;
        }

        if ($a_mode != IL_INSERT_CHILD) {            // determine parent hierarchical id
            // of sibling at $a_pos
            $pos = explode("_", $a_pos);
            $target_pos = array_pop($pos);
            $parent_pos = implode("_", $pos);
        } else {        // if we should insert a child, $a_pos is alreade the hierarchical id
            // of the parent node
            $parent_pos = $a_pos;
        }

        // get the parent node
        if ($parent_pos != "") {
            $parent_node = $this->getContentDomNode($parent_pos);
        } else {
            $parent_node = $this->getPageObjectNode();
        }

        // count the parent children
        $parent_childs = $parent_node->childNodes;
        $cnt_parent_childs = count($parent_childs);
        switch ($a_mode) {
            // insert new node after sibling at $a_pos
            case IL_INSERT_AFTER:
                //$new_node = $a_cont_obj->getNode();
                if ($succ_node = $curr_node->nextSibling) {
                    $a_cont_node = $succ_node->parentNode->insertBefore($a_cont_node, $succ_node);
                } else {
                    $a_cont_node = $parent_node->appendChild($a_cont_node);
                }
                //$a_cont_obj->setNode($new_node);
                break;

            case IL_INSERT_BEFORE:
                //$new_node = $a_cont_obj->getNode();
                $succ_node = $this->getContentDomNode($a_pos);
                $a_cont_node = $succ_node->parentNode->insertBefore($a_cont_node, $succ_node);
                //$a_cont_obj->setNode($new_node);
                break;

                // insert new node as first child of parent $a_pos (= $a_parent)
            case IL_INSERT_CHILD:
                //$new_node = $a_cont_obj->getNode();
                if ($cnt_parent_childs == 0) {
                    $a_cont_node = $parent_node->appendChild($a_cont_node);
                } else {
                    $a_cont_node = $parent_childs->item(0)->parentNode->insertBefore($a_cont_node, $parent_childs->item(0));
                }
                //$a_cont_obj->setNode($new_node);
                break;
        }
    }

    public function insertContent(
        \ilPageContent $a_cont_obj,
        string $a_pos,
        int $a_mode = IL_INSERT_AFTER,
        string $a_pcid = "",
        bool $remove_placeholder = true,
        bool $placeholder_enabled = false
    ): void {
        if ($a_pcid == "" && $a_pos == "") {
            $a_pos = "pg";
        }
        // move mode into container elements is always INSERT_CHILD
        $curr_node = $this->getContentDomNode($a_pos, $a_pcid);
        $curr_name = $curr_node->nodeName;

        // @todo: try to generalize this
        if (($curr_name == "TableData") || ($curr_name == "PageObject") ||
            ($curr_name == "ListItem") || ($curr_name == "Section")
            || ($curr_name == "Tab") || ($curr_name == "ContentPopup")
            || ($curr_name == "GridCell")) {
            $a_mode = IL_INSERT_CHILD;
        }

        $hid = $curr_node->getAttribute("HierId");
        if ($hid != "") {
            //echo "-".$a_pos."-".$hid."-";
            $a_pos = $hid;
        }

        if ($a_mode != IL_INSERT_CHILD) {            // determine parent hierarchical id
            // of sibling at $a_pos
            $pos = explode("_", $a_pos);
            $target_pos = array_pop($pos);
            $parent_pos = implode("_", $pos);
        } else {        // if we should insert a child, $a_pos is alreade the hierarchical id
            // of the parent node
            $parent_pos = $a_pos;
        }

        // get the parent node
        if ($parent_pos != "") {
            $parent_node = $this->getContentDomNode($parent_pos);
        } else {
            $parent_node = $this->getPageObjectNode();
        }

        // count the parent children
        $parent_childs = $parent_node->childNodes;
        $cnt_parent_childs = count($parent_childs);
        //echo "ZZ$a_mode";
        switch ($a_mode) {
            // insert new node after sibling at $a_pos
            case IL_INSERT_AFTER:
                $new_node = $a_cont_obj->getDomNode();
                //$a_pos = ilPageContent::incEdId($a_pos);
                //$curr_node = $this->getContentNode($a_pos);
                //echo "behind $a_pos:";
                if ($succ_node = $curr_node->nextSibling) {
                    $new_node = $succ_node->parentNode->insertBefore($new_node, $succ_node);
                } else {
                    //echo "movin doin append_child";
                    $new_node = $parent_node->appendChild($new_node);
                }
                $a_cont_obj->setDomNode($new_node);
                break;

            case IL_INSERT_BEFORE:
                //echo "INSERT_BEF";
                $new_node = $a_cont_obj->getDomNode();
                $succ_node = $this->getContentDomNode($a_pos);
                $new_node = $succ_node->parentNode->insertBefore($new_node, $succ_node);
                $a_cont_obj->setDomNode($new_node);
                break;

                // insert new node as first child of parent $a_pos (= $a_parent)
            case IL_INSERT_CHILD:
                //echo "insert as child:parent_childs:$cnt_parent_childs:<br>";
                $new_node = $a_cont_obj->getDomNode();
                if ($cnt_parent_childs == 0) {
                    $new_node = $parent_node->appendChild($new_node);
                } else {
                    $new_node = $parent_childs[0]->parentNode->insertBefore($new_node, $parent_childs[0]);
                }
                $a_cont_obj->setDomNode($new_node);
                //echo "PP";
                break;
        }

        //check for PlaceHolder to remove in EditMode-keep in Layout Mode
        if ($remove_placeholder && !$placeholder_enabled) {
            foreach ($curr_node->childNodes as $sub_node) {
                if ($sub_node->nodeName == "PlaceHolder") {
                    $curr_node->parentNode->removeChild($curr_node);
                }
            }
        }
    }

    /**
     * move content object from position $a_source before position $a_target
     * (both hierarchical content ids)
     * @param string $a_source
     * @param string $a_target
     * @param string $a_spcid
     * @param string $a_tpcid
     * @return array|bool
     * @throws ilCOPagePCEditException
     * @throws ilCOPageUnknownPCTypeException
     * @throws ilDateTimeException
     */
    public function moveContentAfter(
        \ilPageObject $page,
        string $a_source,
        string $a_target,
        string $a_spcid = "",
        string $a_tpcid = ""
    ): void {
        // nothing to do...
        if ($a_source === $a_target) {
            return;
        }

        // clone the node
        $content = $page->getContentObject($a_source, $a_spcid);
        $source_node = $content?->getDomNode();
        $clone_node = $source_node?->cloneNode(true);

        // delete source node
        $this->deleteContent($page, $a_source, $a_spcid, false);

        // insert cloned node at target
        if ($content) {
            $content->setDomNode($clone_node);
            $this->insertContent($content, $a_target, IL_INSERT_AFTER, $a_tpcid);
        }
    }

    /**
     * Copy contents to clipboard
     */
    public function copyContents(
        array $a_hids,
        \ilObjUser $user
    ): void {
        $pc_id = null;

        if (!is_array($a_hids)) {
            return;
        }

        $time = date("Y-m-d H:i:s", time());

        $hier_ids = array();
        $skip = array();
        foreach ($a_hids as $a_hid) {
            if ($a_hid == "") {
                continue;
            }
            $a_hid = explode(":", $a_hid);

            // check, whether new hid is child of existing one or vice versa
            reset($hier_ids);
            foreach ($hier_ids as $h) {
                if ($h . "_" == substr($a_hid[0], 0, strlen($h) + 1)) {
                    $skip[] = $a_hid[0];
                }
                if ($a_hid[0] . "_" == substr($h, 0, strlen($a_hid[0]) + 1)) {
                    $skip[] = $h;
                }
            }
            $pc_id[$a_hid[0]] = $a_hid[1];
            if ($a_hid[0] != "") {
                $hier_ids[$a_hid[0]] = $a_hid[0];
            }
        }
        foreach ($skip as $s) {
            unset($hier_ids[$s]);
        }
        $hier_ids = \ilPageContent::sortHierIds($hier_ids);
        $nr = 1;
        foreach ($hier_ids as $hid) {
            $curr_node = $this->getContentDomNode($hid, $pc_id[$hid]);
            if (is_object($curr_node)) {
                if ($curr_node->nodeName == "PageContent") {
                    $content = $this->dom_util->dump($curr_node);
                    // remove pc and hier ids
                    $content = preg_replace('/PCID=\"[a-z0-9]*\"/i', "", $content);
                    $content = preg_replace('/HierId=\"[a-z0-9_]*\"/i', "", $content);

                    $user->addToPCClipboard($content, $time, $nr);
                    $nr++;
                }
            }
        }
        \ilEditClipboard::setAction("copy");
    }


    //
    // Specific methods, should go to other components
    //
}
