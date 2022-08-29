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
 * Class ilPCFileList
 * File List content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCFileList extends ilPageContent
{
    public php4DOMElement $list_node;

    public function init(): void
    {
        $this->setType("flst");
    }

    public function setNode(php4DOMElement $a_node): void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->list_node = $a_node->first_child();		// this is the Table node
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->list_node = $this->dom->create_element("FileList");
        $this->list_node = $this->node->append_child($this->list_node);
    }

    public function appendItem(
        int $a_id,
        string $a_location,
        string $a_format
    ): void {
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

    public function setListTitle(
        string $a_title,
        string $a_language
    ): void {
        ilDOMUtil::setFirstOptionalElement(
            $this->dom,
            $this->list_node,
            "Title",
            array("FileItem"),
            $a_title,
            array("Language" => $a_language)
        );
    }

    public function getListTitle(): string
    {
        $chlds = $this->list_node->child_nodes();
        for ($i = 0; $i < count($chlds); $i++) {
            if ($chlds[$i]->node_name() == "Title") {
                return $chlds[$i]->get_content();
            }
        }
        return "";
    }

    public function getLanguage(): string
    {
        $chlds = $this->list_node->child_nodes();
        for ($i = 0; $i < count($chlds); $i++) {
            if ($chlds[$i]->node_name() == "Title") {
                return $chlds[$i]->get_attribute("Language");
            }
        }
        return "";
    }

    /**
     * Get list of files
     */
    public function getFileList(): array
    {
        $files = array();

        // File Item
        $childs = $this->list_node->child_nodes();
        for ($i = 0; $i < count($childs); $i++) {
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
    public function deleteFileItems(array $a_ids): void
    {
        // File Item
        $childs = $this->list_node->child_nodes();

        for ($i = 0; $i < count($childs); $i++) {
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
    public function savePositions(array $a_pos): void
    {
        asort($a_pos);

        // File Item
        $childs = $this->list_node->child_nodes();
        $nodes = array();
        for ($i = 0; $i < count($childs); $i++) {
            if ($childs[$i]->node_name() == "FileItem") {
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
    public function getAllClasses(): array
    {
        $classes = array();

        // File Item
        $childs = $this->list_node->child_nodes();

        for ($i = 0; $i < count($childs); $i++) {
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
    public function saveStyleClasses(array $a_class): void
    {
        // File Item
        $childs = $this->list_node->child_nodes();
        for ($i = 0; $i < count($childs); $i++) {
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
    public static function getLangVars(): array
    {
        return array("ed_edit_files", "ed_insert_filelist", "pc_flist");
    }

    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ): void {
        if (!$a_page->getImportMode()) {
            // pc filelist
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
     */
    public static function beforePageDelete(
        ilPageObject $a_page
    ): void {
        $files = self::collectFileItems($a_page, $a_page->getDomDoc());

        // delete all file usages
        ilObjFile::_deleteAllUsages(
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            false,
            $a_page->getLanguage()
        );

        foreach ($files as $file_id) {
            if (ilObject::_exists($file_id)) {
                $file_obj = new ilObjFile($file_id, false);
                $file_obj->delete();
            }
        }
    }

    /**
     * After page history entry has been created
     */
    public static function afterPageHistoryEntry(
        ilPageObject $a_page,
        DOMDocument $a_old_domdoc,
        string $a_old_xml,
        int $a_old_nr
    ): void {
        self::saveFileUsage($a_page, $a_old_domdoc, $a_old_nr);
    }

    /**
     * Save file usages
     */
    public static function saveFileUsage(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        int $a_old_nr = 0
    ): void {
        $file_ids = self::collectFileItems($a_page, $a_domdoc);
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
    public static function collectFileItems(
        ilPageObject $a_page,
        DOMDocument $a_domdoc
    ): array {
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

    public static function deleteHistoryLowerEqualThan(
        string $parent_type,
        int $page_id,
        string $lang,
        int $delete_lower_than_nr
    ): void {
        $file_ids = self::_deleteHistoryUsagesLowerEqualThan(
            $parent_type,
            $page_id,
            $delete_lower_than_nr,
            $lang
        );

        foreach ($file_ids as $file_id) {
            $file = new ilObjFile($file_id, false);
            $usages = $file->getUsages();
            if (count($usages) == 0) {
                $file->delete();
            }
        }
    }

    protected static function _deleteHistoryUsagesLowerEqualThan(
        string $parent_type,
        int $a_id,
        int $a_usage_hist_nr,
        string $a_lang = "-"
    ): array {
        global $DIC;

        $hist_repo = $DIC->copage()->internal()->repo()->history();

        $file_ids = [];
        foreach ($hist_repo->getHistoryNumbersOlderEqualThanNr(
            $a_usage_hist_nr,
            $parent_type,
            $a_id,
            $a_lang
        ) as $old_nr) {
            foreach (ilObjFile::_getFilesOfObject($parent_type . ":pg", $a_id, $old_nr, $a_lang) as $file_id) {
                $file_ids[$file_id] = $file_id;
            }
            ilObjFile::_deleteAllUsages($parent_type . ":pg", $a_id, $old_nr, $a_lang);
        }
        return $file_ids;
    }
}
