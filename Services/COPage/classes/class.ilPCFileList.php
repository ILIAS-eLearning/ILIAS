<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCFileList
*
* File List content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileList extends ilPageContent
{
    public $list_node;

    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("flst");
    }

    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->list_node = $a_node->first_child();		// this is the Table node
    }

    public function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->list_node = $this->dom->create_element("FileList");
        $this->list_node = $this->node->append_child($this->list_node);
    }

    /*
    function addItems($a_nr)
    {
        for ($i=1; $i<=$a_nr; $i++)
        {
            $new_item = $this->dom->create_element("ListItem");
            $new_item = $this->list_node->append_child($new_item);
        }
    }*/

    public function appendItem($a_id, $a_location, $a_format)
    {
        // File Item
        $new_item = $this->dom->create_element("FileItem");
        $new_item = $this->list_node->append_child($new_item);

        // Identifier
        $id_node = $this->dom->create_element("Identifier");
        $id_node = $new_item->append_child($id_node);
        $id_node->set_attribute("Catalog", "ILIAS");
        $id_node->set_attribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom->create_element("Location");
        $loc_node = $new_item->append_child($loc_node);
        $loc_node->set_attribute("Type", "LocalFile");
        $loc_node->set_content($a_location);

        // Format
        $form_node = $this->dom->create_element("Format");
        $form_node = $new_item->append_child($form_node);
        $form_node->set_content($a_format);
    }

    public function setListTitle($a_title, $a_language)
    {
        ilDOMUtil::setFirstOptionalElement(
            $this->dom,
            $this->list_node,
            "Title",
            array("FileItem"),
            $a_title,
            array("Language" => $a_language)
        );
    }

    public function getListTitle()
    {
        $chlds = $this->list_node->child_nodes();
        for ($i=0; $i<count($chlds); $i++) {
            if ($chlds[$i]->node_name() == "Title") {
                return $chlds[$i]->get_content();
            }
        }
        return "";
    }

    public function getLanguage()
    {
        $chlds = $this->list_node->child_nodes();
        for ($i=0; $i<count($chlds); $i++) {
            if ($chlds[$i]->node_name() == "Title") {
                return $chlds[$i]->get_attribute("Language");
            }
        }
        return "";
    }
    
    /**
    * Get list of files
    */
    public function getFileList()
    {
        $files = array();
        
        // File Item
        $childs = $this->list_node->child_nodes();
        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
                $id = $entry = "";
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $class = $childs[$i]->get_attribute("Class");
                
                // Identifier
                $id_node = $childs[$i]->first_child();
                if ($id_node->node_name() == "Identifier") {
                    $entry = $id_node->get_attribute("Entry");
                    if (substr($entry, 0, 9) == "il__file_") {
                        $id = substr($entry, 9);
                    }
                }
                $files[] = array("entry" => $entry, "id" => $id,
                    "pc_id" => $pc_id, "hier_id" => $hier_id,
                    "class" => $class);
            }
        }
        
        return $files;
    }

    /**
    * Delete file items
    */
    public function deleteFileItems($a_ids)
    {
        $files = array();
        
        // File Item
        $childs = $this->list_node->child_nodes();

        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
                $id = $entry = "";
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                
                if (in_array($hier_id . ":" . $pc_id, $a_ids)) {
                    $childs[$i]->unlink($childs[$i]);
                }
            }
        }
    }

    /**
    * Save positions of file items
    */
    public function savePositions($a_pos)
    {
        asort($a_pos);
        
        // File Item
        $childs = $this->list_node->child_nodes();
        $nodes = array();
        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
                $id = $entry = "";
                $pc_id = $childs[$i]->get_attribute("PCID");
                $hier_id = $childs[$i]->get_attribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $childs[$i];
                $childs[$i]->unlink($childs[$i]);
            }
        }
        
        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->list_node->append_child($nodes[$k]);
            }
        }
    }

    /**
    * Get all style classes
    */
    public function getAllClasses()
    {
        $classes = array();
        
        // File Item
        $childs = $this->list_node->child_nodes();

        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
                $classes[$childs[$i]->get_attribute("HierId") . ":" .
                    $childs[$i]->get_attribute("PCID")] = $childs[$i]->get_attribute("Class");
            }
        }
        
        return $classes;
    }

    /**
    * Save style classes of file items
    */
    public function saveStyleClasses($a_class)
    {
        // File Item
        $childs = $this->list_node->child_nodes();
        for ($i=0; $i<count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
                $childs[$i]->set_attribute(
                    "Class",
                    $a_class[$childs[$i]->get_attribute("HierId") . ":" .
                    $childs[$i]->get_attribute("PCID")]
                );
            }
        }
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_edit_files", "ed_insert_filelist", "pc_flist");
    }

    /**
     * After page has been updated (or created)
     *
     * @param object $a_page page object
     * @param DOMDocument $a_domdoc dom document
     * @param string $a_xml xml
     * @param bool $a_creation true on creation, otherwise false
     */
    public static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
    {
        if (!$a_page->getImportMode()) {
            // pc filelist
            include_once("./Modules/File/classes/class.ilObjFile.php");
            $file_ids = ilObjFile::_getFilesOfObject(
                $a_page->getParentType() . ":pg",
                $a_page->getId(),
                0,
                $a_page->getLanguage()
            );
            self::saveFileUsage($a_page, $a_domdoc);

            foreach ($file_ids as $file) {	// check, whether file object can be deleted
                if (ilObject::_exists($file) && ilObject::_lookupType($file) == "file") {
                    $file_obj = new ilObjFile($file, false);
                    $usages = $file_obj->getUsages();
                    if (count($usages) == 0) {	// delete, if no usage exists
                        if ($file_obj->getMode() == "filelist") {		// non-repository object
                            $file_obj->delete();
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Before page is being deleted
     *
     * @param object $a_page page object
     */
    public static function beforePageDelete($a_page)
    {
        $files = self::collectFileItems($a_page, $a_page->getDomDoc());
        
        // delete all file usages
        include_once("./Modules/File/classes/class.ilObjFile.php");
        ilObjFile::_deleteAllUsages(
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            false,
            $a_page->getLanguage()
        );

        include_once("./Modules/File/classes/class.ilObjFile.php");
        foreach ($files as $file_id) {
            if (ilObject::_exists($file_id)) {
                $file_obj = new ilObjFile($file_id, false);
                $file_obj->delete();
            }
        }
    }

    /**
     * After page history entry has been created
     *
     * @param object $a_page page object
     * @param DOMDocument $a_old_domdoc old dom document
     * @param string $a_old_xml old xml
     * @param integer $a_old_nr history number
     */
    public static function afterPageHistoryEntry($a_page, DOMDocument $a_old_domdoc, $a_old_xml, $a_old_nr)
    {
        self::saveFileUsage($a_page, $a_old_domdoc, $a_old_nr);
    }

    /**
     * Save file usages
     */
    public static function saveFileUsage($a_page, $a_domdoc, $a_old_nr = 0)
    {
        $file_ids = self::collectFileItems($a_page, $a_domdoc);
        include_once("./Modules/File/classes/class.ilObjFile.php");
        ilObjFile::_deleteAllUsages($a_page->getParentType() . ":pg", $a_page->getId(), $a_old_nr, $a_page->getLanguage());
        foreach ($file_ids as $file_id) {
            ilObjFile::_saveUsage(
                $file_id,
                $a_page->getParentType() . ":pg",
                $a_page->getId(),
                $a_old_nr,
                $a_page->getLanguage()
            );
        }
    }

    /**
     * Get all file items that are used within the page
     */
    public static function collectFileItems($a_page, $a_domdoc)
    {
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//FileItem/Identifier');
        $file_ids = array();
        foreach ($nodes as $node) {
            $id_arr = explode("_", $node->getAttribute("Entry"));
            $file_id = $id_arr[count($id_arr) - 1];
            if ($file_id > 0 && ($id_arr[1] == "" || $id_arr[1] == IL_INST_ID || $id_arr[1] == 0)) {
                $file_ids[$file_id] = $file_id;
            }
        }
        // file items in download links
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//IntLink[@Type='File']");
        foreach ($nodes as $node) {
            $t = $node->getAttribute("Target");
            if (substr($t, 0, 9) == "il__dfile") {
                $id_arr = explode("_", $t);
                $file_id = $id_arr[count($id_arr) - 1];
                $file_ids[$file_id] = $file_id;
            }
        }
        return $file_ids;
    }
}
