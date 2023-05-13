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
    public function init(): void
    {
        $this->setType("flst");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "FileList");
    }

    public function appendItem(
        int $a_id,
        string $a_location,
        string $a_format
    ): void {
        // File Item
        $new_item = $this->dom_doc->createElement("FileItem");
        $new_item = $this->getChildNode()->appendChild($new_item);

        // Identifier
        $id_node = $this->dom_doc->createElement("Identifier");
        $id_node = $new_item->appendChild($id_node);
        $id_node->setAttribute("Catalog", "ILIAS");
        $id_node->setAttribute("Entry", "il__file_" . $a_id);

        // Location
        $loc_node = $this->dom_doc->createElement("Location");
        $loc_node = $new_item->appendChild($loc_node);
        $loc_node->setAttribute("Type", "LocalFile");
        $this->dom_util->setContent($loc_node, $a_location);

        // Format
        $form_node = $this->dom_doc->createElement("Format");
        $form_node = $new_item->appendChild($form_node);
        $this->dom_util->setContent($form_node, $a_format);
    }

    public function setListTitle(
        string $a_title,
        string $a_language
    ): void {
        $this->dom_util->setFirstOptionalElement(
            $this->getChildNode(),
            "Title",
            array("FileItem"),
            $a_title,
            array("Language" => $a_language)
        );
    }

    public function getListTitle(): string
    {
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "Title") {
                return $c->nodeValue;
            }
        }
        return "";
    }

    public function getLanguage(): string
    {
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "Title") {
                return $c->getAttribute("Language");
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
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "FileItem") {
                $id = $entry = "";
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $class = $c->getAttribute("Class");

                // Identifier
                $id_node = $c->firstChild;
                if ($id_node->nodeName === "Identifier") {
                    $entry = $id_node->getAttribute("Entry");
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
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "FileItem") {
                $id = $entry = "";
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");

                if (in_array($hier_id . ":" . $pc_id, $a_ids)) {
                    $c->parentNode->removeChild($c);
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
        $nodes = array();
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "FileItem") {
                $pc_id = $c->getAttribute("PCID");
                $hier_id = $c->getAttribute("HierId");
                $nodes[$hier_id . ":" . $pc_id] = $c;
            }
        }
        $this->dom_util->deleteAllChildsByName($this->getChildNode(), ["FileItem"]);
        foreach ($a_pos as $k => $v) {
            if (is_object($nodes[$k])) {
                $nodes[$k] = $this->getChildNode()->appendChild($nodes[$k]);
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
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName === "FileItem") {
                $classes[$c->getAttribute("HierId") . ":" .
                    $c->getAttribute("PCID")] = $c->getAttribute("Class");
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
        foreach ($this->getChildNode()->childNodes as $c) {
            if ($c->nodeName == "FileItem") {
                $c->setAttribute(
                    "Class",
                    $a_class[$c->getAttribute("HierId") . ":" .
                    $c->getAttribute("PCID")]
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
                (int) $file_id,
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
            if (ilObject::_lookupType($file_id) === "file") {
                $file = new ilObjFile($file_id, false);
                $usages = $file->getUsages();
                if (count($usages) == 0) {
                    $file->delete();
                }
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
