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
 * File Based Learning Module (HTML) object
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFileBasedLM extends ilObject
{
    protected ?string $start_file = null;
    protected bool $online;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        // default is offline
        $this->setOfflineStatus(true);

        $this->db = $DIC->database();
        // this also calls read() method! (if $a_id is set)
        $this->type = "htlm";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function update(bool $a_skip_meta = false): bool
    {
        $ilDB = $this->db;

        if (!$a_skip_meta) {
            $this->updateMetaData();
        }
        parent::update();

        $ilDB->manipulate($q = "UPDATE file_based_lm SET " .
            " startfile = " . $ilDB->quote($this->getStartFile(), "text") . " " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        return true;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        $q = "SELECT * FROM file_based_lm WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $lm_set = $ilDB->query($q);
        $lm_rec = $ilDB->fetchAssoc($lm_set);
        $this->setStartFile((string) $lm_rec["startfile"]);
    }

    public function create(bool $a_skip_meta = false): int
    {
        $ilDB = $this->db;

        $id = parent::create();
        $this->createDataDirectory();

        $ilDB->manipulate("INSERT INTO file_based_lm (id, startfile) VALUES " .
            " (" . $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getStartFile(), "text") . ")");
        if (!$a_skip_meta) {
            $this->createMetaData();
        }
        return $id;
    }

    public function getDataDirectory(string $mode = "filesystem"): string
    {
        $lm_data_dir = ilFileUtils::getWebspaceDir($mode) . "/lm_data";
        $lm_dir = $lm_data_dir . "/lm_" . $this->getId();

        return $lm_dir;
    }

    public function createDataDirectory(): void
    {
        ilFileUtils::makeDir($this->getDataDirectory());
    }

    public function getStartFile(): ?string
    {
        return $this->start_file;
    }

    public function setStartFile(
        string $a_file,
        bool $a_omit_file_check = false
    ): void {
        if ($a_file &&
            (file_exists($this->getDataDirectory() . "/" . $a_file) || $a_omit_file_check)) {
            $this->start_file = $a_file;
        }
    }


    public function delete(): bool
    {
        $ilDB = $this->db;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // Delete meta data
        $this->deleteMetaData();

        // delete file_based_lm record
        $ilDB->manipulate("DELETE FROM file_based_lm WHERE id = " .
            $ilDB->quote($this->getId(), "integer"));

        // delete data directory
        ilFileUtils::delDir($this->getDataDirectory());

        return true;
    }

    /**
     * Populate by directory. Add a filename to do a special check for
     * ILIAS HTML export files. If the corresponding directory is found
     * within the passed directory path (i.e. "htlm_<id>") this
     * subdirectory is used instead.
     */
    public function populateByDirectoy(
        string $a_dir,
        string $a_filename = ""
    ): void {
        preg_match("/.*htlm_([0-9]*)\.zip/", $a_filename, $match);
        if (is_dir($a_dir . "/htlm_" . $match[1])) {
            $a_dir .= "/htlm_" . $match[1];
        }
        ilFileUtils::rCopy($a_dir, $this->getDataDirectory());
        ilFileUtils::renameExecutables($this->getDataDirectory());
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        /** @var ilObjFileBasedLM $new_obj */
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($new_obj);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus($this->getOfflineStatus());
        } else {
            $new_obj->setOfflineStatus(true);
        }

        // copy content
        $new_obj->populateByDirectoy($this->getDataDirectory());

        $new_obj->setStartFile($this->getStartFile());
        $new_obj->update();

        return $new_obj;
    }

    public function isInfoEnabled(): bool
    {
        return ilObjContentObjectAccess::isInfoEnabled($this->getId());
    }
}
