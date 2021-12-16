<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * Class ilObjFileImplementationLegacy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileImplementationLegacy extends ilObjFileImplementationAbstract implements ilObjFileImplementationInterface
{
    /**
     * @var int
     */
    protected $obj_id;

    protected $version;
    /**
     * @var \ilFSStorageFile
     */
    private $file_storage;
    /**
     * @var string
     */
    protected $file_name;

    /**
     * ilObjFileImplementationAbstract constructor.
     * @param int    $obj_id
     * @param int    $version
     * @param string $file_name
     */
    public function __construct(int $obj_id, int $version, string $file_name)
    {
        $this->obj_id = $obj_id;
        $this->version = $version;
        $this->file_name = $file_name;
        $this->file_storage = new ilFSStorageFile($obj_id);
        $this->file_storage->create();
    }

    /**
     * @inheritDoc
     */
    public function getDirectory($a_version = 0)
    {
        $version_subdir = "";

        if ($a_version) {
            $version_subdir = sprintf("%03d", $a_version);
        }

        return $this->file_storage->getAbsolutePath() . '/' . $version_subdir;
    }

    /**
     * @inheritDoc
     */
    public function createDirectory()
    {
        ilUtil::makeDirParents($this->getDirectory());
    }

    /**
     * @inheritDoc
     */
    public function clearDataDirectory()
    {
        ilUtil::delDir($this->getDirectory());
        $this->createDirectory();
    }

    /**
     * @inheritDoc
     */
    public function deleteVersions($a_hist_entry_ids = null)
    {
        if ($a_hist_entry_ids == null || count($a_hist_entry_ids) < 1) {
            $this->clearDataDirectory();

            ilHistory::_removeEntriesForObject($this->obj_id);
        } else {
            $actualVersionDeleted = false;

            // get all versions
            $versions = $this->getVersions();

            // delete each version
            foreach ($a_hist_entry_ids as $hist_id) {
                $entry = null;

                // get version
                foreach ($versions as $index => $version) {
                    if ($version["hist_entry_id"] == $hist_id) {
                        // remove each history entry
                        ilHistory::_removeEntryByHistoryID($hist_id);

                        // delete directory
                        $version_dir = $this->getDirectory($version["version"]);
                        ilUtil::delDir($version_dir);

                        // is actual version?
                        if ($version["version"] == $this->getVersion()) {
                            $actualVersionDeleted = true;
                        }

                        // remove from array
                        unset($versions[$index]);
                        break;
                    }
                }
            }

            // update actual version if it was deleted before
            if ($actualVersionDeleted) {
                // get newest version (already sorted by getVersions)
                $version = reset($versions);
                $version['max_version'] = $this->getMaxVersion();
                $this->updateWithVersion($version);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sendFile($a_hist_entry_id = null)
    {
        if (is_null($a_hist_entry_id)) {
            $file = $this->getFile();
            $file = ilFileUtils::getValidFilename($file);
        } else {
            $entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);
            $data = ilObjFileImplementationLegacy::parseInfoParams($entry);
            $file = $this->getDirectory($data["version"]) . "/" . $data["filename"];
        }
        global $DIC;
        if ($this->file_storage->fileExists($file)) {
            $ilClientIniFile = $DIC['ilClientIniFile'];
            /**
             * @var $ilClientIniFile ilIniFile
             */

            $ilFileDelivery = new \ilFileDelivery($file);
            $ilFileDelivery->setDisposition($this->isInline() ? ilFileDelivery::DISP_INLINE : ilFileDelivery::DISP_ATTACHMENT);
            $ilFileDelivery->setConvertFileNameToAsci((bool) !$ilClientIniFile->readVariable('file_access',
                'disable_ascii'));

            // also returning the 'real' filename if a history file is delivered
            if ($ilClientIniFile->readVariable('file_access', 'download_with_uploaded_filename')
                != '1'
                && is_null($a_hist_entry_id)
            ) {
                $ilFileDelivery->setDownloadFileName(ilFileUtils::getValidFilename($this->getFileName()));
            } else {
                // $download_file_name = basename($file);
                /* FSX Info: basename has a Bug with Japanese and other characters, see:
                 * http://stackoverflow.com/questions/32115609/basename-fail-when-file-name-start-by-an-accent
                 * Therefore we can no longer use basename();
                 */
                $parts = explode(DIRECTORY_SEPARATOR, $file);
                $download_file_name = end($parts);
                $download_file_name = ilFileUtils::getValidFilename($download_file_name);
                $ilFileDelivery->setDownloadFileName($download_file_name);
            }
            $ilFileDelivery->deliver();
        }

        throw new FileNotFoundException($DIC->language()->txt('file_not_found_sec'));
    }

    /**
     * @inheritDoc
     */
    public function getFileExtension()
    {
        return ilObjFileAccess::_getFileExtension($this->getTitle());
    }

    /**
     * Compares two file versions.
     * @param array $v1 First file version to compare.
     * @param array $v2 Second file version to compare.
     * @return int Returns an integer less than, equal to, or greater than zero if the first
     *                  argument is considered to be respectively less than, equal to, or greater than
     *                  the second.
     */
    protected function compareVersions($v1, $v2)
    {
        // v2 - v1 because version should be descending
        return (int) $v2["version"] - (int) $v1["version"];
    }

    public function export($a_target_dir)
    {
        $subdir = "il_" . IL_INST_ID . "_file_" . $this->getId();
        ilUtil::makeDir($a_target_dir . "/objects/" . $subdir);

        $filedir = $this->getDirectory($this->getVersion()); // FSX

        if (@!is_dir($filedir)) {
            $filedir = $this->getDirectory();
        }

        ilUtil::rCopy($filedir, $a_target_dir . "/objects/" . $subdir);
    }

    /**
     * @inheritDoc
     *            array (
     * 0 =>
     * array (
     * 'date' => '2020-11-05 09:49:18',
     * 'user_id' => '6',
     * 'obj_id' => '297',
     * 'obj_type' => 'file',
     * 'action' => 'create',
     * 'info_params' => 'Version 1.docx,1,1',
     * 'user_comment' => '',
     * 'hist_entry_id' => '1',
     * 'title' => NULL,
     * 'filename' => 'Version 1.docx',
     * 'version' => '1',
     * 'max_version' => '1',
     * 'rollback_version' => '',
     * 'rollback_user_id' => '',
     * ),
     * )
     */
    public function getVersions($version_ids = null) : array
    {
        $versions = (array) ilHistory::_getEntriesForObject($this->obj_id, 'file');

        if ($version_ids != null && count($version_ids) > 0) {
            foreach ($versions as $index => $version) {
                if (!in_array($version["hist_entry_id"], $version_ids, true)) {
                    unset($versions[$index]);
                }
            }
        }

        // add custom entries
        foreach ($versions as $index => $version) {
            $params = ilObjFileImplementationLegacy::parseInfoParams($version);
            $versions[$index] = array_merge($version, $params);
        }

        // sort by version number (hist_entry_id will do for that)
        usort($versions, array($this, "compareVersions"));

        $version_objects = [];
        foreach ($versions as $version) {
            $file = $this->getDirectory($version["version"]) . "/" . $version["filename"];
            $filesize = @filesize($file);
            $version['size'] = $filesize;
            $version_objects[] = new ilObjFileVersion($version);
        }

        return $version_objects;
    }

    /**
     * Parses the info parameters ("info_params") of the specified history entry.
     * @param array $entry The history entry.
     * @return array Returns an array containing the "filename" and "version" contained within the
     *                     "info_params".
     */
    public static function parseInfoParams($entry) : array
    {
        $data = explode(",", $entry["info_params"]);

        // bugfix: first created file had no version number
        // this is a workaround for all files created before the bug was fixed
        if (empty($data[1])) {
            $data[1] = "1";
        }

        if (empty($data[2])) {
            $data[2] = "1";
        }

        // BEGIN bugfix #31730
        // if more than 2 commas are detected, the need for reassembling the filename is: possible to necessary
        if (sizeof($data) > 2)
        {
          $last = sizeof($data) - 1;
          for ($n = 1; $n < $last - 1; $n++)
          {
            $data[0] .= "," . $data[$n];
          }

          // trying to distinguish the next-to-last being a 'last part of the filename'
          // or a 'version information',  based on having a dot included or not
          if (strpos($data[$last - 1], ".") !== false)
          {
            $data[0] .= "," . $data[$last - 1];
            $data[1] = $data[$last];
            $data[2] = $data[$last];
          }
          else
          {
            $data[1] = $data[$last - 1];
            $data[2] = $data[$last];
          }
        }
        // END bugfix #31730
        
        $result = array(
            "filename" => $data[0],
            "version" => $data[1],
            "max_version" => $data[2],
            "rollback_version" => "",
            "rollback_user_id" => "",
        );

        // if rollback, the version contains the rollback version as well
        if ($entry["action"] == "rollback") {
            $tokens = explode("|", $result["max_version"]);
            if (count($tokens) > 1) {
                $result["max_version"] = $tokens[0];
                $result["rollback_version"] = $tokens[1];

                if (count($tokens) > 2) {
                    $result["rollback_user_id"] = $tokens[2];
                }
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getSpecificVersion($version_id)
    {
        include_once("./Services/History/classes/class.ilHistory.php");
        $version = ilHistory::_getEntryByHistoryID($version_id);
        if ($version === false) {
            return false;
        }

        // ilHistory returns different keys in _getEntryByHistoryID and _getEntriesForObject
        // so this makes it the same
        $version["hist_entry_id"] = $version["id"];
        $version["user_id"] = $version["usr_id"];
        $version["date"] = $version["hdate"];
        unset($version["id"], $version["usr_id"], $version["hdate"]);

        // parse params
        $params = ilObjFileImplementationLegacy::parseInfoParams($version);

        return array_merge($version, $params);
    }

    /**
     * @inheritDoc
     */
    public function getFile($a_hist_entry_id = null)
    {
        if (is_null($a_hist_entry_id)) {
            $file = $this->getDirectory($this->getVersion()) . "/" . $this->getFileName(); // FSX
        } else {
            require_once("./Services/History/classes/class.ilHistory.php");
            $entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);

            if ($entry === false) {
                return false;
            }

            $data = ilObjFileImplementationLegacy::parseInfoParams($entry);
            $file = $this->getDirectory($data["version"]) . "/" . $data["filename"];
        }

        return $file;
    }

    public function getVersion()
    {
        return $this->version;
    }

    private function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @return bool
     * @deprecated
     */
    private function isInline()
    {
        return ilObjFileAccess::_isFileInline($this->getFileName());
    }

    /**
     * @inheritDoc
     */
    public function getFileType()
    {
        return '';
    }

    public function getStorageID() : ?string
    {
        return '-';
    }

}
