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

namespace ILIAS\COPage\Compare;

use ILIAS\COPage\Dom\DomUtil;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PageCompare
{
    protected DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;

        $this->dom_util = $DIC->copage()
                              ->internal()
                              ->domain()
                              ->domUtil();
    }

    public function compare(
        \ilPageObject $main_page,
        \ilPageObject $l_page,
        \ilPageObject $r_page,
    ): array {
        $main_page->preparePageForCompare($l_page);
        $main_page->preparePageForCompare($r_page);
        $l_hashes = $this->getPageContentsHashes($l_page);
        $r_hashes = $this->getPageContentsHashes($r_page);
        // determine all deleted and changed page elements
        foreach ($l_hashes as $pc_id => $h) {
            if (!isset($r_hashes[$pc_id])) {
                $l_hashes[$pc_id]["change"] = "Deleted";
            } else {
                if ($h["hash"] != $r_hashes[$pc_id]["hash"]) {
                    $l_hashes[$pc_id]["change"] = "Modified";
                    $r_hashes[$pc_id]["change"] = "Modified";

                    // if modified element is a paragraph, highlight changes
                    if ($l_hashes[$pc_id]["content"] != "" &&
                        $r_hashes[$pc_id]["content"] != "") {
                        $new_left = str_replace("\n", "<br />", $l_hashes[$pc_id]["content"]);
                        $new_right = str_replace("\n", "<br />", $r_hashes[$pc_id]["content"]);
                        $wldiff = new \WordLevelDiff(
                            array($new_left),
                            array($new_right)
                        );
                        $new_left = $wldiff->orig();
                        $new_right = $wldiff->closing();
                        $this->setParagraphContent($l_page, $l_hashes[$pc_id]["hier_id"], $new_left[0]);
                        $this->setParagraphContent($r_page, $l_hashes[$pc_id]["hier_id"], $new_right[0]);
                    }
                }
            }
        }

        // determine all new paragraphs
        foreach ($r_hashes as $pc_id => $h) {
            if (!isset($l_hashes[$pc_id])) {
                $r_hashes[$pc_id]["change"] = "New";
            }
        }
        $this->addChangeDivClasses($l_page, $l_hashes);
        $this->addChangeDivClasses($r_page, $r_hashes);

        return array("l_page" => $l_page,
                     "r_page" => $r_page,
                     "l_changes" => $l_hashes,
                     "r_changes" => $r_hashes
        );
    }

    protected function setParagraphContent(
        \ilPageObject $page,
        string $a_hier_id,
        string $content
    ): void {
        $node = $page->getContentDomNode($a_hier_id);
        if (is_object($node)) {
            $this->dom_util->setContent($node, $content);
        }
    }

    protected function addChangeDivClasses(
        \ilPageObject $page,
        array $a_hashes
    ): void {
        $dom = $page->getDomDoc();
        $path = "/*[1]";
        $nodes = $this->dom_util->path($dom, $path);
        $rnode = $nodes->item(0);
        foreach ($a_hashes as $h) {
            if (($h["change"] ?? "") != "") {
                $dc_node = $dom->createElement("DivClass");
                $dc_node->setAttribute("HierId", $h["hier_id"]);
                $dc_node->setAttribute("Class", "ilEdit" . $h["change"]);
                $dc_node = $rnode->appendChild($dc_node);
            }
        }
    }


    /**
     * Get page contents hashes
     */
    protected function getPageContentsHashes(\ilPageObject $page): array
    {
        $page->buildDom();
        $page->addHierIDs();
        $dom = $page->getDomDoc();

        // get existing ids
        $path = "//PageContent";
        $hashes = array();
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $hier_id = $node->getAttribute("HierId");
            $pc_id = $node->getAttribute("PCID");
            $dump = $this->dom_util->dump($node);
            if (($hpos = strpos($dump, ' HierId="' . $hier_id . '"')) > 0) {
                $dump = substr($dump, 0, $hpos) .
                    substr($dump, $hpos + strlen(' HierId="' . $hier_id . '"'));
            }

            $childs = $node->childNodes;
            $content = "";
            if ($childs->item(0) && $childs->item(0)->nodeName == "Paragraph") {
                $content = $this->dom_util->dump($childs->item(0));
                $content = substr(
                    $content,
                    strpos($content, ">") + 1,
                    strrpos($content, "<") - (strpos($content, ">") + 1)
                );
                $content = \ilPCParagraph::xml2output($content);
            }
            $hashes[$pc_id] =
                array("hier_id" => $hier_id, "hash" => md5($dump), "content" => $content);
        }

        return $hashes;
    }
}
