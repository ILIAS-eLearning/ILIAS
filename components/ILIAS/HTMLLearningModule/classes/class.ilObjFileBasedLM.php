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

use ILIAS\Filesystem\Util\Archive\Zip;
use ILIAS\Filesystem\Util\Archive\ZipOptions;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Resource\StorableContainerResource;

/**
 * File Based Learning Module (HTML) object
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjFileBasedLM extends ilObject
{
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\Filesystem\Util\Archive\Archives $archives;
    protected ?string $start_file = null;
    protected ?string $rid = null;
    protected bool $online;

    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;


        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
        $this->archives = $DIC->archives();
        // this also calls read() method! (if $a_id is set)
        $this->type = "htlm";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function update(bool $a_skip_meta = false): bool
    {
        if (!$a_skip_meta) {
            $this->updateMetaData();
        }
        parent::update();

        $this->db->update(
            'file_based_lm',
            [
                'startfile' => ['text', $this->getStartFile()],
                'rid' => ['text', $this->getRID() ?? '']
            ],
            ['id' => ['integer', $this->getId()]]
        );

        return true;
    }

    public function read(): void
    {
        parent::read();

        $q = "SELECT * FROM file_based_lm WHERE id = " . $this->db->quote($this->getId(), "integer");
        $lm_set = $this->db->query($q);
        $lm_rec = $this->db->fetchAssoc($lm_set);
        $this->setStartFile((string) ($lm_rec["startfile"] ?? ''));
        $this->setRID((string) ($lm_rec["rid"] ?? ''));
    }

    public function create(bool $a_skip_meta = false): int
    {
        $id = parent::create();

        // create empty container resource. empty zips are not allowed, we need at least one file which is hidden
        $empty_zip = $this->archives->zip(
            []
        );

        $rid = $this->irss->manageContainer()->containerFromStream(
            $empty_zip->get(),
            new ilHTLMStakeholder(),
            $this->getTitle()
        );
        $this->setRID($rid->serialize());

        $this->db->insert(
            'file_based_lm',
            [
                'id' => ['integer', $this->getId()],
                'startfile' => ['text', $this->getStartFile()],
                'rid' => ['text', $this->getRID()]
            ]
        );


        if (!$a_skip_meta) {
            $this->createMetaData();
        }
        return $id;
    }

    public function maybeDetermineStartFile(): bool
    {
        $valid_start_files = ["index.htm", "index.html", "start.htm", "start.html"];
        /** @var StorableContainerResource $resource */
        $resource = $this->getResource();
        if ($resource !== null) {
            $zip = $this->irss->consume()->containerZIP($resource->getIdentification())->getZIP();
            foreach ($zip->getFiles() as $file) {
                if (in_array(basename($file), $valid_start_files, true)) {
                    $this->setStartFile($file);
                    $this->update();
                    return true;
                }
            }
        }
        return false;
    }

    public function getDataDirectory(string $mode = "filesystem"): string
    {
        return CLIENT_WEB_DIR . "/lm_data" . "/lm_" . $this->getId();
    }

    public function createDataDirectory(): void
    {
        //
    }

    public function getStartFile(): ?string
    {
        return $this->start_file;
    }

    public function setStartFile(
        string $a_file,
        bool $a_omit_file_check = false
    ): void {
        $this->start_file = $a_file;
    }

    public function getRID(): ?string
    {
        return $this->rid;
    }

    public function getResource(): ?StorableContainerResource
    {
        if ($this->getRID() === null) {
            return null;
        }
        $rid = $this->irss->manage()->find($this->getRID());
        if ($rid === null) {
            return null;
        }
        return $this->irss->manage()->getResource($rid);
    }

    public function setRID(string $rid): void
    {
        $this->rid = $rid;
    }


    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // Delete meta data
        $this->deleteMetaData();

        // delete file_based_lm record
        $this->db->manipulateF(
            "DELETE FROM file_based_lm WHERE id = %s",
            ["integer"],
            [$this->getId()]
        );

        // delete data directory
        ilFileUtils::delDir($this->getDataDirectory()); // for legacy reasons
        // TODO remove RID

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
        if (is_dir($a_dir . "/htlm_" . ($match[1] ?? ""))) {
            $a_dir .= "/htlm_" . ($match[1] ?? "");
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

        $new_obj->setStartFile((string) $this->getStartFile());
        $new_obj->update();

        return $new_obj;
    }

    public function isInfoEnabled(): bool
    {
        return ilObjContentObjectAccess::isInfoEnabled($this->getId());
    }
}
