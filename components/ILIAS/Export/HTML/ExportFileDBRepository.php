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

namespace ILIAS\Export\HTML;

use ilDBInterface;
use ILIAS\Repository\IRSS\IRSSWrapper;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\components\ResourceStorage\Container\Wrapper\ZipReader;

class ExportFileDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected IRSSWrapper $irss,
        protected DataService $data,
        protected \ilExportHTMLStakeholder $stakeholder
    )
    {
    }

    public function create(
        int $object_id,
        string $type,
        string $title
    ): string
    {
        $rid = $this->irss->createContainer(
            $this->stakeholder,
            $title
        );
        $this->db->insert('export_files_html', [
            'object_id' => ['integer', $object_id],
            'rid' => ['text', $rid],
            'timestamp' => ['timestamp', \ilUtil::now()],
            'type' => ['text', $type]
        ]);
        return $rid;
    }

    public function addString(
        string $rid,
        string $content,
        string $path,
    ): void {
        $this->irss->addStringToContainer(
            $rid,
            $content,
            $path
        );
    }

    public function addFile(
        string $rid,
        string $fullpath,
        string $path,
    ): void {
        $this->irss->addLocalFileToContainer(
            $rid,
            $fullpath,
            $path
        );
    }

    public function addDirectory(
        string $rid,
        string $source_dir,
        string $target_path = ""
    ): void
    {
        $this->irss->addDirectoryToContainer(
            $rid,
            $source_dir,
            $target_path
        );
    }

    public function addContainerDirToTargetContainer(
        string $source_container_id,
        string $target_container_id,
        string $source_dir_path = "",
        string $target_dir_path = ""
    ): void {
        $this->irss->addContainerDirToTargetContainer(
            $source_container_id,
            $target_container_id,
            $source_dir_path,
            $target_dir_path
        );
    }

    public function update(ExportFile $file): void
    {
        $this->db->update('export_files_html', [
            'timestamp' => ['timestamp', $file->getTimestamp()],
            'type' => ['text', $file->getType()]
        ], [
            'object_id' => ['integer', $file->getObjectId()],
            'rid' => ['text', $file->getRid()]
        ]);
    }

    public function delete(
        int $object_id,
        string $rid
    ): void
    {
        $this->irss->deleteResource(
            $rid,
            $this->stakeholder
        );
        $this->db->manipulateF(
            'DELETE FROM export_files_html WHERE object_id = %s AND rid = %s',
            ['integer', 'text'],
            [$object_id, $rid]
        );
    }

    public function getFilePath(
        string $rid
    ): string
    {
        return $this->irss->getResourcePath($rid);
    }

    public function getById(int $object_id, string $rid): ?ExportFile
    {
        $set = $this->db->queryF(
            'SELECT * FROM export_files_html WHERE object_id = %s AND rid = %s',
            ['integer', 'text'],
            [$object_id, $rid]
        );

        $record = $this->db->fetchAssoc($set);
        return $record ? $this->getExportFileFromRecord($record) : null;
    }

    /**
     * @return \Generator<ExportFile>
     */
    public function getAllOfObjectId(int $object_id): \Generator
    {
        $set = $this->db->queryF("SELECT * FROM export_files_html " .
            " WHERE object_id = %s ORDER BY timestamp DESC",
            ["integer"],
            [$object_id]
        );
        while ($record = $this->db->fetchAssoc($set)) {
            yield $this->getExportFileFromRecord($record);
        }
    }

    public function getLatestOfObjectIdAndType(int $object_id, string $type = ""): ?ExportFile
    {
        $set = $this->db->queryF("SELECT * FROM export_files_html " .
            " WHERE object_id = %s AND type = %s ORDER BY timestamp DESC",
            ["integer", "text"],
            [$object_id, $type]
        );
        if ($record = $this->db->fetchAssoc($set)) {
            return $this->getExportFileFromRecord($record);
        }
        return null;
    }

    public function getResourceIdForIdString(string $rid): ?ResourceIdentification
    {
        return $this->irss->getResourceIdForIdString($rid);
    }

    protected function getExportFileFromRecord(array $record): ExportFile
    {
        return $this->data->exportFile(
            (int) $record['object_id'],
            (string) $record['rid'],
            (string) $record['timestamp'],
            (string) $record['type']
        );
    }

    public function deliverFile(string $rid): void
    {
        $this->irss->deliverFile($rid);
    }

    // currently broken, see https://mantis.ilias.de/view.php?id=44135
    public function rename(
        string $rid,
        string $title
    ): void
    {
        $this->irss->renameContainer($rid, $title);
    }


}