<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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
