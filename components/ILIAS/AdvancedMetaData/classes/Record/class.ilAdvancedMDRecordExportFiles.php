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

use ILIAS\AdvancedMetaData\Record\File\Factory as ilAMDRecordFileFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 * @todo    use logger and filestorage
 */
class ilAdvancedMDRecordExportFiles
{
    protected const SIZE = 'size';
    protected const DATE = 'date';
    protected const NAME = 'name';
    protected ilAMDRecordFileFactory $amd_record_file_factory;
    protected string $export_dir = '';
    protected ObjectId|null $object_id;
    protected int $user_id;

    public function __construct(
        int $user_id,
        ?ObjectId $a_obj_id = null
    ) {
        $this->amd_record_file_factory = new ilAMDRecordFileFactory();
        $this->user_id = $user_id;
        $this->object_id = $a_obj_id;
    }

    /**
     * @return array array e.g array(records => 'ECS-Server',size => '123',created' => 121212)
     */
    public function readFilesInfo(): array
    {
        $file_info = [];
        $elements = is_null($this->object_id)
            ? $this->amd_record_file_factory->handler()->getGlobalFiles()
            : $this->amd_record_file_factory->handler()->getFilesByObjectId($this->object_id);
        foreach ($elements as $element) {
            $file_id = $element->getIRSS()->getResourceIdSerialized();
            $file_info[$file_id][self::SIZE] = $element->getIRSS()->getResourceSize();
            $file_info[$file_id][self::DATE] = $element->getIRSS()->getCreationDate()->getTimestamp();
            $file_info[$file_id][self::NAME] = $element->getIRSS()->getRecords();
        }
        return $file_info;
    }

    public function create(
        string $a_xml
    ): void {
        $file_name = time() . '.xml';
        $stream = Streams::ofString($a_xml);
        if (is_null($this->object_id)) {
            $this->amd_record_file_factory->handler()->addGlobalFile(
                $this->user_id,
                $file_name,
                $stream
            );
        }
        if (!is_null($this->object_id)) {
            $this->amd_record_file_factory->handler()->addFile(
                $this->object_id,
                $this->user_id,
                $file_name,
                $stream
            );
        }
    }

    public function download(
        string $file_id,
        string|null $filename_overwrite = null
    ): void {
        if (is_null($this->object_id)) {
            $this->amd_record_file_factory->handler()->downloadGlobal(
                $file_id,
                $filename_overwrite
            );
        }
        if (!is_null($this->object_id)) {
            $this->amd_record_file_factory->handler()->download(
                $this->object_id,
                $file_id,
                $filename_overwrite
            );
        }
    }

    public function deleteByFileId(
        int $user_id,
        string $file_id
    ): bool {
        $global = is_null($this->object_id);
        if ($global) {
            $this->amd_record_file_factory->handler()->deleteGlobal(
                $user_id,
                $file_id
            );
        }
        if (!$global) {
            $this->amd_record_file_factory->handler()->delete(
                $this->object_id,
                $user_id,
                $file_id
            );
        }
        return true;
    }
}
