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

namespace ILIAS\COPage\ID;

use ILIAS\COPage\Dom\DomUtil;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ContentIdManager
{
    protected const ID_ELEMENTS =
        ["PageContent",
         "TableRow",
         "TableData",
         "ListItem",
         "FileItem",
         "Section",
         "Tab",
         "ContentPopup",
         "GridCell"];

    protected array $hier_ids = [];
    protected \ilPageObject $page;
    protected DomUtil $dom_util;

    public function __construct(
        \ilPageObject $page
    ) {
        global $DIC;

        $this->dom_util = $DIC->copage()
            ->internal()
            ->domain()
            ->domUtil();
        $this->page = $page;
    }

    /**
     * Add hierarchical ID (e.g. for editing) attributes "HierId" to current dom tree.
     * This attribute will be added to the following elements:
     * PageObject, Paragraph, Table, TableRow, TableData.
     * Only elements of these types are counted as "childs" here.
     * Hierarchical IDs have the format "x_y_z_...", e.g. "1_4_2" means: second
     * child of fourth child of first child of page.
     * The PageObject element gets the special id "pg". The first child of the
     * page starts with id 1. The next child gets the 2 and so on.
     * Another example: The first child of the page is a Paragraph -> id 1.
     * The second child is a table -> id 2. The first row gets the id 2_1, the
     */
    public function addHierIDsToDom(): void
    {
        $this->hier_ids = [];
        $dom = $this->page->getDomDoc();

        $sep = $path = "";
        foreach (self::ID_ELEMENTS as $el) {
            $path .= $sep . "//" . $el;
            $sep = " | ";
        }

        $nodes = $this->dom_util->path($dom, $path);

        foreach ($nodes as $node) {
            $cnode = $node;
            $ctag = $cnode->nodeName;

            // get hierarchical id of previous sibling
            $sib_hier_id = "";
            while ($cnode = $cnode->previousSibling) {
                if (($cnode->nodeType == XML_ELEMENT_NODE)
                    && $cnode->hasAttribute("HierId")) {
                    $sib_hier_id = $cnode->getAttribute("HierId");
                    break;
                }
            }

            if ($sib_hier_id != "") {        // set id to sibling id "+ 1"
                $node_hier_id = $this->incEdId($sib_hier_id);
                $node->setAttribute("HierId", $node_hier_id);
                $this->hier_ids[] = $node_hier_id;
            } else {                        // no sibling -> node is first child
                // get hierarchical id of next parent
                $cnode = $node;
                $par_hier_id = "";
                while ($cnode = $cnode->parentNode) {
                    if (($cnode->nodeType == XML_ELEMENT_NODE)
                        && $cnode->hasAttribute("HierId")) {
                        $par_hier_id = $cnode->getAttribute("HierId");
                        break;
                    }
                }
                if (($par_hier_id != "") && ($par_hier_id != "pg")) {        // set id to parent_id."_1"
                    $node_hier_id = $par_hier_id . "_1";
                    $node->setAttribute("HierId", $node_hier_id);
                    $this->hier_ids[] = $node_hier_id;
                } else {        // no sibling, no parent -> first node
                    $node_hier_id = "1";
                    $node->setAttribute("HierId", $node_hier_id);
                    $this->hier_ids[] = $node_hier_id;
                }
            }
        }

        // set special hierarchical id "pg" for pageobject
        $path = "//PageObject";
        $nodes = $this->dom_util->path($dom, $path);
        foreach ($nodes as $node) {
            $node->setAttribute("HierId", "pg");
            $this->hier_ids[] = "pg";
        }
    }

    /**
     * get all hierarchical ids
     */
    public function getHierIds(): array
    {
        return $this->hier_ids;
    }

    public function stripHierIDsFromDom(): void
    {
        $this->hier_ids = [];
        $dom = $this->page->getDomDoc();

        if (is_object($dom)) {
            $path = "//*[@HierId]";
            $nodes = $this->dom_util->path($dom, $path);
            foreach ($nodes as $node) {
                if ($node->hasAttribute("HierId")) {
                    $node->removeAttribute("HierId");
                }
            }
        }
    }


    /**
     * Increases an hierarchical editing id at lowest level (last number)
     * @param string $ed_id hierarchical ID
     * @return string hierarchical ID (increased)
     */
    protected function incEdId(string $ed_id): string
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]++;
        return implode("_", $id);
    }

    /**
     * Decreases an hierarchical editing id at lowest level (last number)
     * @param string $ed_id hierarchical ID
     * @return string hierarchical ID (decreased)
     */
    protected function decEdId(string $ed_id): string
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]--;
        return implode("_", $id);
    }
}
