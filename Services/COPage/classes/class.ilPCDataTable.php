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

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode(
            $a_hier_id,
            $a_pc_id,
            "Table",
            ["Language" => "", "DataTable" => "y"]
        );
    }


    public function makeEmptyCell(DomNode $td_node): void
    {
        // delete children of paragraph node
        foreach ($td_node->childNodes as $child) {
            $td_node->removeChild($child);
        }

        // create page content and paragraph node here.
        $pc_node = $this->getNewPageContentNode();
        $pc_node = $td_node->appendChild($pc_node);
        $par_node = $this->dom_doc->createElement("Paragraph");
        $par_node = $pc_node->appendChild($par_node);
        $par_node->setAttribute("Characteristic", "TableContent");
        $par_node->setAttribute(
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
        $error = "";
        if (is_array($a_data)) {
            foreach ($a_data as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $cell) {
                        $temp_dom = $this->dom_util->docFromString(
                            '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $cell . '</Paragraph>',
                            $error
                        );
                        $par_node = $this->getCellNode($i, $j, true);
                        // remove all childs
                        if (empty($error) && !is_null($par_node)) {
                            // delete children of paragraph node
                            foreach ($par_node->childNodes as $child) {
                                $par_node->removeChild($child);
                            }

                            // copy new content children in paragraph node
                            $nodes = $this->dom_util->path(
                                $temp_dom,
                                "//Paragraph"
                            );

                            if (count($nodes) == 1) {
                                $new_par_node = $nodes->item(0);
                                foreach ($new_par_node->childNodes as $c) {
                                    $cloned_child = $c->cloneNode(true);
                                    $cloned_child = $this->dom_doc->importNode($cloned_child);
                                    $par_node->appendChild($cloned_child);
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
