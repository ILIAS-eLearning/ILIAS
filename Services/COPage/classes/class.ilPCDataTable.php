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
 * Class ilPCDataTable
 *
 * Data table content object (see ILIAS DTD). This type of table can only hold
 * one paragraph content item per cell.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCDataTable extends ilPCTable
{
    public function init(): void
    {
        $this->setType("dtab");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->tab_node = $a_node->first_child();		// this is the Table node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->tab_node = $this->dom->create_element("Table");
        $this->tab_node = $this->node->append_child($this->tab_node);
        $this->tab_node->set_attribute("Language", "");
        $this->tab_node->set_attribute("DataTable", "y");
    }


    /**
     * Make cell empty
     */
    public function makeEmptyCell(php4DOMElement $td_node): void
    {
        // delete children of paragraph node
        $children = $td_node->child_nodes();
        for ($i = 0; $i < count($children); $i++) {
            $td_node->remove_child($children[$i]);
        }

        // create page content and paragraph node here.
        $pc_node = $this->createPageContentNode(false);
        $pc_node = $td_node->append_child($pc_node);
        $par_node = $this->dom->create_element("Paragraph");
        $par_node = $pc_node->append_child($par_node);
        $par_node->set_attribute("Characteristic", "TableContent");
        $par_node->set_attribute(
            "Language",
            $this->getLanguage()
        );
    }


    /**
     * Set data of cells
     * @return bool|array
     */
    public function setData(array $a_data)
    {
        if (is_array($a_data)) {
            foreach ($a_data as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $cell) {
                        $temp_dom = domxml_open_mem(
                            '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $cell . '</Paragraph>',
                            DOMXML_LOAD_PARSING,
                            $error
                        );

                        $par_node = $this->getCellNode($i, $j, true);
                        // remove all childs
                        if (empty($error) && is_object($par_node)) {
                            // delete children of paragraph node
                            $children = $par_node->child_nodes();
                            for ($k = 0; $k < count($children); $k++) {
                                $par_node->remove_child($children[$k]);
                            }

                            // copy new content children in paragraph node
                            $xpc = xpath_new_context($temp_dom);
                            $path = "//Paragraph";
                            $res = xpath_eval($xpc, $path);

                            if (count($res->nodeset) == 1) {
                                $new_par_node = $res->nodeset[0];
                                $new_childs = $new_par_node->child_nodes();
                                for ($l = 0; $l < count($new_childs); $l++) {
                                    $cloned_child = $new_childs[$l]->clone_node(true);
                                    $par_node->append_child($cloned_child);
                                }
                            }
                        } else {
                            if (!empty($error)) {
                                return $error;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }
}
