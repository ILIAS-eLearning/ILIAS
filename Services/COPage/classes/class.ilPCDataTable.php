<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Services/COPage/classes/class.ilPageContent.php");
require_once("./Services/COPage/classes/class.ilPCTable.php");

/**
* Class ilPCDataTable
*
* Data table content object (see ILIAS DTD). This type of table can only hold
* one paragraph content item per cell.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCDataTable extends ilPCTable
{
    public $dom;
    public $tab_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("dtab");
    }

    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->tab_node = $a_node->first_child();		// this is the Table node
    }

    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->tab_node = $this->dom->create_element("Table");
        $this->tab_node = $this->node->append_child($this->tab_node);
        $this->tab_node->set_attribute("Language", "");
        $this->tab_node->set_attribute("DataTable", "y");
    }
    
    /**
    * Get cell paragraph node of row $i and cell $j
    */
    public function getCellNode($i, $j)
    {
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow[$i+1]/TableData[$j+1]/PageContent[1]/Paragraph[1]";
        //echo "<br>++".$path;
        //]--//PageContent[@HierId='3']/Table/TableRow[+1]/TableData[0 style=+1]/PageContent[1]/Paragraph[1]
        $res = xpath_eval($xpc, $path);

        if (is_object($res->nodeset[0])) {
            return $res->nodeset[0];
        } else {		// no node -> delete all childs and create paragraph
            $xpc2 = xpath_new_context($this->dom);
            $path2 = "//PageContent[@HierId='" . $this->getHierId() . "']" .
                "/Table/TableRow[" . ($i+1) . "]/TableData[" . ($j+1) . "]";
            //$path2 = "//PageContent";
            
            $res2 = xpath_eval($xpc2, $path2);

            $td_node = $res2->nodeset[0];

            if (is_object($td_node)) {
                // delete children of paragraph node
                $children = $td_node->child_nodes();
                for ($i=0; $i<count($children); $i++) {
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

                return $par_node;
            }
        }
        
        return "";
    }
    
    /**
    * Make cell empty
    */
    public function makeEmptyCell($td_node)
    {
        // delete children of paragraph node
        $children = $td_node->child_nodes();
        for ($i=0; $i<count($children); $i++) {
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
    * Get cell text of row $i and cell $j
    */
    public function getCellText($i, $j)
    {
        $cell_par = $this->getCellNode($i, $j);
        
        if (is_object($cell_par)) {
            $content = "";
            $childs = $cell_par->child_nodes();
            for ($i=0; $i<count($childs); $i++) {
                $content.= $this->dom->dump_node($childs[$i]);
            }
            return $content;
        } else {
            return "";
        }
    }

    /**
    * Set data of cells
    */
    public function setData($a_data)
    {
        $ok = true;
        //var_dump($a_data);
        if (is_array($a_data)) {
            foreach ($a_data as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $cell) {
                        //echo "<br><br>=".$cell."=";
                        $temp_dom = @domxml_open_mem(
                            '<?xml version="1.0" encoding="UTF-8"?><Paragraph>' . $cell . '</Paragraph>',
                            DOMXML_LOAD_PARSING,
                            $error
                        );

                        $par_node = $this->getCellNode($i, $j);
                        //echo "<br>=".htmlentities($this->dom->dump_node($par_node))."=";
                        //echo "<br>-$i-$j-$cell-";
                        // remove all childs
                        if (empty($error) && is_object($par_node)) {
                            // delete children of paragraph node
                            $children = $par_node->child_nodes();
                            for ($k=0; $k < count($children); $k++) {
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
                                    //echo "<br>=".htmlentities($this->dom->dump_node($cloned_child))."=";
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
        //exit;
        return true;
    }
}
