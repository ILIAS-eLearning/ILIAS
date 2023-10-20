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

/**
 * store prg-files
 */
class ilFSStoragePRG extends ilFileSystemAbstractionStorage
{
    protected const MEMBER_EXPORT_DIR = 'assingmentsExport';

    public function __construct(int $a_container_id = 0)
    {
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $a_container_id);
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix(): string
    {
        return 'prg';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix(): string
    {
        return 'StudyProgramme';
    }

    public function getMemberExportDirectory(): string
    {
        return $this->getAbsolutePath() . '/' . self::MEMBER_EXPORT_DIR;
    }

    public function initMemberExportDirectory(): void
    {
        ilFileUtils::makeDirParents($this->getMemberExportDirectory());
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
            if (
                preg_match(
                    "/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/",
                    $file,
                    $matches
                )
                && $matches[3] == $this->getContainerId()
            ) {
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

    public function getMemberExportFile(string $filename): string
    {
        $file_name = $this->getMemberExportDirectory() . '/' . $filename;

        if (file_exists($file_name)) {
            return file_get_contents($file_name);
        } else {
            throw new \Exception('file not found:' . $filename);
        }
        return '';
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

    public function deleteMemberExportFile(string $filename): bool
    {
        return $this->deleteFile($this->getMemberExportDirectory() . '/' . $filename);
    }

    public function hasMemberExportFile(string $filename): bool
    {
        return $this->fileExists($this->getMemberExportDirectory() . '/' . $filename);
    }
}
