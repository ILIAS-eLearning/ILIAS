<?php

declare(strict_types=0);
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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilFSStorageCourse extends ilFileSystemAbstractionStorage
{
    public const MEMBER_EXPORT_DIR = 'memberExport';
    public const INFO_DIR = 'info';
    public const ARCHIVE_DIR = 'archives';

    private ilLogger $logger;

    public function __construct(int $a_container_id = 0)
    {
        global $DIC;

        /** @noinspection PhpUndefinedMethodInspection */
        $this->logger = $DIC->logger()->crs();
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $a_container_id);
    }

    public static function _clone(int $a_source_id, int $a_target_id): bool
    {
        $source = new ilFSStorageCourse($a_source_id);
        $target = new ilFSStorageCourse($a_target_id);
        $target->create();
        ilFileSystemAbstractionStorage::_copyDirectory($source->getAbsolutePath(), $target->getAbsolutePath());

        // Delete member export files
        $target->deleteDirectory($target->getMemberExportDirectory());

        unset($source);
        unset($target);
        return true;
    }

    public function initInfoDirectory(): void
    {
        ilFileUtils::makeDirParents($this->getInfoDirectory());
    }

    public function getInfoDirectory(): string
    {
        return $this->getAbsolutePath() . '/' . self::INFO_DIR;
    }

    public function initMemberExportDirectory(): void
    {
        ilFileUtils::makeDirParents($this->getMemberExportDirectory());
    }

    public function getMemberExportDirectory(): string
    {
        return $this->getAbsolutePath() . '/' . self::MEMBER_EXPORT_DIR;
    }

    public function addMemberExportFile($a_data, $a_rel_name): bool
    {
        $this->initMemberExportDirectory();
        if (!$this->writeToFile($a_data, $this->getMemberExportDirectory() . '/' . $a_rel_name)) {
            $this->logger->write('Cannot write to file: ' . $this->getMemberExportDirectory() . '/' . $a_rel_name);
            return false;
        }

        return true;
    }

    public function getMemberExportFiles(): array
    {
        if (!is_dir($this->getMemberExportDirectory())) {
            return array();
        }
        $dp = opendir($this->getMemberExportDirectory());

        $files = [];
        while ($file = readdir($dp)) {
            if (is_dir($file)) {
                continue;
            }

            if (preg_match(
                "/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/",
                $file,
                $matches
            ) && $matches[3] == $this->getContainerId()) {
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

    public function getMemberExportFile(string $a_name): string
    {
        $file_name = $this->getMemberExportDirectory() . '/' . $a_name;

        if (file_exists($file_name)) {
            return file_get_contents($file_name);
        }
        return '';
    }

    public function deleteMemberExportFile(string $a_export_name): bool
    {
        return $this->deleteFile($this->getMemberExportDirectory() . '/' . $a_export_name);
    }

    /**
     * Implementation of abstract method
     * @access protected
     */
    protected function getPathPostfix(): string
    {
        return 'crs';
    }

    /**
     * Implementation of abstract method
     * @access protected
     */
    protected function getPathPrefix(): string
    {
        return 'ilCourse';
    }
}
