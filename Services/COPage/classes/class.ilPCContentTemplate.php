<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Content templates are not existing in the page. Once they are inserted into a page
 * all content elements of the template are inserted instead.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCContentTemplate extends ilPageContent
{
    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("templ");
    }

    /**
     * Set node (in fact this will never be called, since these types of nodes do not exist
     */
    public function setNode($a_node)
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
        $source_page->addHierIds();
        $hier_ids = $source_page->getHierIds();

        $copy_ids = array();
        foreach ($hier_ids as $hier_id) {
            // move top level nodes only
            if (!is_int(strpos($hier_id, "_"))) {
                if ($hier_id != "pg" && $hier_id >= $a_hid) {
                    $copy_ids[] = $hier_id;
                }
            }
        }
        asort($copy_ids);

        // get the target parent node
        $pos = explode("_", $a_pos);
        array_pop($pos);
        $parent_pos = implode($pos, "_");
        if ($parent_pos != "") {
            $target_parent = $a_pg_obj->getContentNode($parent_pos);
        } else {
            $target_parent = $a_pg_obj->getNode();
        }

        //$source_parent = $source_page->getContentNode("pg");

        $curr_node = $a_pg_obj->getContentNode($a_hier_id, $a_pcid);

        foreach ($copy_ids as $copy_id) {
            $source_node = $source_page->getContentNode($copy_id);
            $new_node = $source_node->clone_node(true);
            $new_node->unlink_node($new_node);

            if ($succ_node = $curr_node->next_sibling()) {
                $succ_node->insert_before($new_node, $succ_node);
            } else {
                //echo "movin doin append_child";
                $target_parent->append_child($new_node);
            }

            //$xpc = xpath_new_context($a_pg_obj->getDomDoc());
            $xpath = new DOMXpath($a_pg_obj->getDomDoc());
            //var_dump($new_node->myDOMNode);
            //echo "-".$new_node->get_attribute("PCID")."-"; exit;
            if ($new_node->get_attribute("PCID") != "") {
                $new_node->set_attribute("PCID", "");
            }
            $els = $xpath->query(".//*[@PCID]", $new_node->myDOMNode);
            foreach ($els as $el) {
                $el->setAttribute("PCID", "");
            }
            $curr_node = $new_node;
        }

        $a_pg_obj->update();

        //$this->node = $this->createPageContentNode();

        /*$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->map_node =& $this->dom->create_element("Map");
        $this->map_node =& $this->node->append_child($this->map_node);
        $this->map_node->set_attribute("Latitude", "0");
        $this->map_node->set_attribute("Longitude", "0");
        $this->map_node->set_attribute("Zoom", "3");*/
    }
}
