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

/**
 * Layout templates are not existing in the page. Once they are inserted into a page
 * all content elements of the template are inserted instead.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCLayoutTemplate extends ilPageContent
{
    public function init(): void
    {
        $this->setType("lay");
    }

    public function create(ilPageObject $a_pg_obj, string $a_hier_id, string $a_pc_id, int $a_tmpl): void
    {
        $source_page = ilPageObjectFactory::getInstance("stys", $a_tmpl);
        $source_page->buildDom();
        $source_page->addHierIDs();
        $hier_ids = $source_page->getHierIds();

        $copy_ids = array();
        foreach ($hier_ids as $hier_id) {
            // move top level nodes only
            if (!is_int(strpos($hier_id, "_"))) {
                if ($hier_id != "pg") {
                    $copy_ids[] = $hier_id;
                }
            }
        }
        arsort($copy_ids);

        foreach ($copy_ids as $copy_id) {
            $source_content = $source_page->getContentObject($copy_id);

            $source_node = $source_content->getDomNode();
            $clone_node = $source_node->cloneNode(true);
            $this->log->debug($this->dom_util->dump($clone_node));
            //$clone_node->unlink_node($clone_node);
            $clone_node = $this->getPage()->getDomDoc()->importNode($clone_node, true);

            // insert cloned node at target
            $source_content->setDomNode($clone_node);
            $this->getPage()->insertContent($source_content, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);

            $xpath = new DOMXpath($this->getPage()->getDomDoc());
            if ($clone_node->getAttribute("PCID") != "") {
                $clone_node->setAttribute("PCID", "");
            }
            $els = $xpath->query(".//*[@PCID]", $clone_node);
            foreach ($els as $el) {
                $el->setAttribute("PCID", "");
            }
        }

        $this->getPage()->update();
    }
}
