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

namespace ILIAS\File\Versions\Table;

use ILIAS\Data\DataSize;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table as I;
use ILIAS\UI\Factory as UIFactory;

class DataRetrieval implements I\DataRetrieval
{
    public const string ACTION_DELETE = 'delete';
    public const string ACTION_ROLLBACK = 'rollback';
    public const string ACTION_PUBLISH = 'publish';
    public const string ACTION_UNPUBLISH = 'unpublish';

    public function __construct(
        private readonly \ilObjFile $file,
        private readonly int $current_version,
        private readonly bool $current_version_is_draft,
        private readonly int $amount_of_versions,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilFileVersionsGUI $parent_gui,
        private readonly \ilLanguage $lng,
        private readonly UIFactory $ui_factory
    ) {
    }

    public function getRows(
        I\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        $records = $this->getRecords($order);
        foreach ($records as $record) {
            $row_id = (string) $record['hist_entry_id'];
            $version_number = (int) $record['version'];
            $is_current = $version_number === $this->current_version;

            yield $row_builder->buildDataRow($row_id, $this->mapRecord($record))
                ->withDisabledAction(
                    self::ACTION_DELETE,
                    $this->current_version_is_draft
                )
                ->withDisabledAction(
                    self::ACTION_ROLLBACK,
                    $this->current_version_is_draft || $is_current
                )
                ->withDisabledAction(
                    self::ACTION_PUBLISH,
                    !($this->current_version_is_draft && $is_current)
                )
                ->withDisabledAction(
                    self::ACTION_UNPUBLISH,
                    $this->current_version_is_draft || !$is_current || $this->amount_of_versions <= 1
                );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->file->getVersions());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRecords(Order $order): array
    {
        $records = [];
        foreach ($this->file->getVersions() as $version) {
            $records[] = $version->getArrayCopy();
        }

        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value): array => [$key, $value]);
        if ($order_field === 'version') {
            usort($records, static fn(array $a, array $b): int => (int) $a['version'] <=> (int) $b['version']);
        } else {
            usort($records, static fn(array $a, array $b): int => ($a[$order_field] ?? null) <=> ($b[$order_field] ?? null));
        }
        if ($order_direction === 'DESC') {
            $records = array_reverse($records);
        }
        return $records;
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private function mapRecord(array $record): array
    {
        $hist_id = (int) $record['hist_entry_id'];

        $name = \ilObjUser::_lookupName((int) $record['user_id']);
        $username = trim(($name['title'] ?? '') . ' ' . ($name['firstname'] ?? '') . ' ' . ($name['lastname'] ?? ''));

        $action_label = $this->lng->txt('file_version_' . $record['action']);
        if ($record['action'] === 'rollback') {
            $rollback_name = \ilObjUser::_lookupName((int) $record['rollback_user_id']);
            $rollback_username = trim(
                ($rollback_name['title'] ?? '') . ' ' .
                ($rollback_name['firstname'] ?? '') . ' ' .
                ($rollback_name['lastname'] ?? '')
            );
            $action_label = sprintf($action_label, $record['rollback_version'], $rollback_username);
        }

        $this->ctrl->setParameter($this->parent_gui, \ilFileVersionsGUI::HIST_ID, $hist_id);
        $download_url = $this->ctrl->getLinkTarget($this->parent_gui, \ilFileVersionsGUI::CMD_DOWNLOAD_VERSION);
        $this->ctrl->setParameter($this->parent_gui, \ilFileVersionsGUI::HIST_ID, '');

        $filename_link = $this->ui_factory->link()->standard((string) $record['filename'], $download_url);

        $size = new DataSize((int) ($record['size'] ?? 0), DataSize::KB);

        return [
            'version' => (int) $record['version'],
            'filename' => $filename_link,
            'date' => new \DateTimeImmutable((string) $record['date']),
            'uploaded_by' => $username,
            'versionname' => (string) ($record['title'] ?? ''),
            'filesize' => (string) $size,
            'status' => $action_label,
        ];
    }
}
