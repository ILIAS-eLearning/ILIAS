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

namespace ILIAS\COPage\Style;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class StyleManager
{
    protected \ilDBInterface $db;
    protected \ILIAS\COPage\Dom\DomUtil $dom_util;

    public function __construct()
    {
        global $DIC;

        $this->dom_util = $DIC->copage()->internal()->domain()->domUtil();
        $this->db = $DIC->database();
    }

    /**
     * Save all style class/template usages
     */
    public function saveStyleUsage(
        \ilPageObject $page,
        \DOMDocument $a_domdoc,
        int $a_old_nr = 0
    ): void {
        $sname = "";
        $stype = "";
        $template = "";
        // media aliases
        $path = "//Paragraph | //Section | //MediaAlias | //FileItem" .
            " | //Table | //TableData | //Tabs | //List";
        $usages = array();
        $nodes = $this->dom_util->path($a_domdoc, $path);
        foreach ($nodes as $node) {
            switch ($node->localName) {
                case "Paragraph":
                    $sname = $node->getAttribute("Characteristic");
                    $stype = "text_block";
                    $template = 0;
                    break;

                case "Section":
                    $sname = $node->getAttribute("Characteristic");
                    $stype = "section";
                    $template = 0;
                    break;

                case "MediaAlias":
                    $sname = $node->getAttribute("Class");
                    $stype = "media_cont";
                    $template = 0;
                    break;

                case "FileItem":
                    $sname = $node->getAttribute("Class");
                    $stype = "flist_li";
                    $template = 0;
                    break;

                case "Table":
                    $sname = $node->getAttribute("Template");
                    if ($sname == "") {
                        $sname = $node->getAttribute("Class");
                        $stype = "table";
                        $template = 0;
                    } else {
                        $stype = "table";
                        $template = 1;
                    }
                    break;

                case "TableData":
                    $sname = $node->getAttribute("Class");
                    $stype = "table_cell";
                    $template = 0;
                    break;

                case "Tabs":
                    $sname = $node->getAttribute("Template");
                    if ($sname != "") {
                        if ($node->getAttribute("Type") == "HorizontalAccordion") {
                            $stype = "haccordion";
                        }
                        if ($node->getAttribute("Type") == "VerticalAccordion") {
                            $stype = "vaccordion";
                        }
                    }
                    $template = 1;
                    break;

                case "List":
                    $sname = $node->getAttribute("Class");
                    if ($node->getAttribute("Type") == "Ordered") {
                        $stype = "list_o";
                    } else {
                        $stype = "list_u";
                    }
                    $template = 0;
                    break;
            }
            if ($sname != "" && $stype != "") {
                $usages[$sname . ":" . $stype . ":" . $template] = array("sname" => $sname,
                                                                         "stype" => $stype,
                                                                         "template" => $template
                );
            }
        }

        $this->deleteStyleUsages($page, $a_old_nr);

        foreach ($usages as $u) {
            $id = $this->db->nextId('page_style_usage');
            $this->db->manipulate("INSERT INTO page_style_usage " .
                "(id, page_id, page_type, page_lang, page_nr, template, stype, sname) VALUES (" .
                $this->db->quote($id, "integer") . "," .
                $this->db->quote($page->getId(), "integer") . "," .
                $this->db->quote($page->getParentType(), "text") . "," .
                $this->db->quote($page->getLanguage(), "text") . "," .
                $this->db->quote($a_old_nr, "integer") . "," .
                $this->db->quote($u["template"], "integer") . "," .
                $this->db->quote($u["stype"], "text") . "," .
                $this->db->quote($u["sname"], "text") .
                ")");
        }
    }

    /**
     * Delete style usages
     */
    public function deleteStyleUsages(
        \ilPageObject $page,
        int $a_old_nr = 0
    ): void {
        $and_old_nr = "";
        if ($a_old_nr !== 0) {
            $and_old_nr = " AND page_nr = " . $this->db->quote($a_old_nr, "integer");
        }

        $this->db->manipulate(
            "DELETE FROM page_style_usage WHERE " .
            " page_id = " . $this->db->quote($page->getId(), "integer") .
            " AND page_type = " . $this->db->quote($page->getParentType(), "text") .
            " AND page_lang = " . $this->db->quote($page->getLanguage(), "text") .
            $and_old_nr
        );
    }
}
