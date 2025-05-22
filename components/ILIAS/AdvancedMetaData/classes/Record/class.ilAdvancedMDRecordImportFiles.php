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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 * @todo    use filesystem service
 */
class ilAdvancedMDRecordImportFiles
{
    public const IMPORT_NAME = 'ilias_adv_md_record';

    private string $import_dir = '';

    public function __construct()
    {
        $this->import_dir = ilFileUtils::getDataDir() . '/ilAdvancedMetaData/import';
        $this->init();
    }

    public function getImportDirectory(): string
    {
        return $this->import_dir;
    }

    /**
     * Get import file by creation date
     * @param int creation date (unix time)
     * @return string absolute path
     */
    public function getImportFileByCreationDate(int $a_unix_time): string
    {
        $unix_time = $a_unix_time;
        return $this->getImportDirectory() . '/' . self::IMPORT_NAME . '_' . $unix_time . '.xml';
    }

    /**
     * Delete a file
     * @param int creation date (unix time)
     */
    public function deleteFileByCreationDate(int $a_unix_time): bool
    {
        $unix_time = $a_unix_time;
        return unlink($this->getImportDirectory() . '/' . self::IMPORT_NAME . '_' . $unix_time . '.xml');
    }

    /**
     * move uploaded files
     * @access public
     * @param string tmp name
     * @return int creation time of newly created file. 0 on error
     */
    public function moveUploadedFile(string $a_temp_name): int
    {
        $creation_time = time();
        $file_name = $this->getImportDirectory() . '/' . self::IMPORT_NAME . '_' . $creation_time . '.xml';

        if (!ilFileUtils::moveUploadedFile($a_temp_name, '', $file_name, false)) {
            return 0;
        }
        return $creation_time;
    }

    /**
     * init function: create import directory, delete old files
     */
    private function init(): void
    {
        if (!is_dir($this->import_dir)) {
            ilFileUtils::makeDirParents($this->import_dir);
        }
    }
}
