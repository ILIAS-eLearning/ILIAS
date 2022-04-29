<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesGroup
*/
class ilFSStorageGroup extends ilFileSystemAbstractionStorage
{
    protected const MEMBER_EXPORT_DIR = 'memberExport';

    private ilLogger $logger;

    public function __construct(int $a_container_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->grp();
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $a_container_id);
    }

    /**
     * Init export directory and create it if it does not exist
     */
    public function initMemberExportDirectory() : void
    {
        ilFileUtils::makeDirParents($this->getMemberExportDirectory());
    }

    /**
     * Get path of export directory
     */
    public function getMemberExportDirectory() : string
    {
        return $this->getAbsolutePath() . '/' . self::MEMBER_EXPORT_DIR;
    }

    public function addMemberExportFile(string $a_data, string $a_rel_name) : bool
    {
        $this->initMemberExportDirectory();
        if (!$this->writeToFile($a_data, $this->getMemberExportDirectory() . '/' . $a_rel_name)) {
            $this->logger->warning('Cannot write to file: ' . $this->getMemberExportDirectory() . '/' . $a_rel_name);
            return false;
        }
        return true;
    }

    /**
     * @return array<int, array{name: string, timest: string, type: string, id: string, size: int}>
     */
    public function getMemberExportFiles() : array
    {
        if (!is_dir($this->getMemberExportDirectory())) {
            return [];
        }

        $dp = opendir($this->getMemberExportDirectory());
        $files = [];
        while ($file = readdir($dp)) {
            if (is_dir($file)) {
                continue;
            }

            if (
                preg_match("/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/", $file, $matches) &&
                $matches[3] == $this->getContainerId()) {
                $timest = $matches[1];
                $file_info['name'] = $matches[0];
                $file_info['timest'] = $matches[1];
                $file_info['type'] = $matches[2];
                $file_info['id'] = $matches[3];
                $file_info['size'] = filesize($this->getMemberExportDirectory() . '/' . $file);

                $files[$timest] = $file_info;
            }
        }
        closedir($dp);
        return $files;
    }

    public function getMemberExportFile(string $a_name) : string
    {
        $file_name = $this->getMemberExportDirectory() . '/' . $a_name;
        if (file_exists($file_name)) {
            return file_get_contents($file_name);
        }
        return '';
    }

    public function deleteMemberExportFile(string $a_export_name) : bool
    {
        return $this->deleteFile($this->getMemberExportDirectory() . '/' . $a_export_name);
    }

    /**
     * @inheritDoc
     */
    protected function getPathPostfix() : string
    {
        return 'grp';
    }

    /**
     * @inheritDoc
     */
    protected function getPathPrefix() : string
    {
        return 'ilGroup';
    }
}
