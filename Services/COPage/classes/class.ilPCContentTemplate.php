<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Content templates are not existing in the page. Once they are inserted into a page
 * all content elements of the template are inserted instead.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCContentTemplate extends ilPageContent
{
    public function init() : void
    {
        $this->setType("templ");
    }

    /**
     * Set node (in fact this will never be called, since these types of nodes do not exist
     */
    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
    }

    /**
     * Insert content template
     *
     * @param ilPageObject $a_pg_obj page object
     * @param string $a_hier_id Hierarchical ID
     * @param string $a_pc_id pc id
     * @param int $a_page_templ template page id
     */
    public function create($a_pg_obj, $a_hier_id, $a_pc_id, $a_page_templ)
    {
        $source_id = explode(":", $a_page_templ);
        $source_page = ilPageObjectFactory::getInstance($source_id[1], $source_id[0]);
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

            $source_node = $source_content->getNode();
            $clone_node = $source_node->clone_node(true);
            $clone_node->unlink_node($clone_node);

            // insert cloned node at target
            $source_content->setNode($clone_node);
            $this->getPage()->insertContent($source_content, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);

            $xpath = new DOMXpath($this->getPage()->getDomDoc());
            if ($clone_node->get_attribute("PCID") != "") {
                $clone_node->set_attribute("PCID", "");
            }
            $els = $xpath->query(".//*[@PCID]", $clone_node->myDOMNode);
            foreach ($els as $el) {
                $el->setAttribute("PCID", "");
            }
        }

        $this->getPage()->update();
    }
}
