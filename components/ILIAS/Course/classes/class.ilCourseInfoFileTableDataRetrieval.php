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

use ILIAS\UI\Component\Table\DataRetrieval as ilTableDataRetrievalInterface;

class ilCourseInfoFileTableDataRetrieval implements ilTableDataRetrievalInterface
{
    /** @var array<int,ilCourseFile> */
    protected array $data;

    public function __construct(
        protected ilObjCourse $course
    ) {
        $this->data = [];
    }

    public function getFileTitle(int $id): string
    {
        return $this->data[$id]->getFileName();
    }

    public function init(): void
    {
        if (!count($files = ilCourseFile::_readFilesByCourse($this->course->getId()))) {
            return;
        }
        $this->data = [];
        foreach ($files as $file) {
            $this->data[$file->getFileId()] = $file;
        }
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        foreach ($this->data as $id => $file) {
            yield $row_builder->buildDataRow(
                $id . '',
                [
                    ilCourseInfoFileTableGUI::TABLE_COL_FILENAME => $file->getFileName(),
                    ilCourseInfoFileTableGUI::TABLE_COL_FILESIZE => $file->getFileSize(),
                    ilCourseInfoFileTableGUI::TABLE_COL_FILETYPE => $file->getFileType(),
                ]
            );
        }
    }

    /**
     * @return array<int>
     */
    public function getAllFileIds(): array
    {
        return array_keys($this->data);
    }

    public function deleteFilesByIds(array $ids): void
    {
        foreach ($ids as $file_id) {
            $file = new ilCourseFile((int) $file_id);
            if ($this->course->getId() == $file->getCourseId()) {
                $file->delete();
            }
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->data);
    }
}
