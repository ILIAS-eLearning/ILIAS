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

namespace ILIAS\MediaCast\Settings;

use ilDBInterface;
use ILIAS\MediaCast\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data,
    ) {
    }

    public function getById(int $id): ?Settings
    {
        $query = "SELECT * FROM il_media_cast_data WHERE id = %s";
        $set = $this->db->queryF($query, ["integer"], [$id]);
        $record = $this->db->fetchAssoc($set);

        if ($record) {
            return $this->getSettingsFromRecord($record);
        }

        return null;
    }

    public function update(Settings $settings): void
    {
        $this->db->update('il_media_cast_data', [
            'public_files' => ['integer', (int) $settings->getPublicFiles()],
            'downloadable' => ['integer', (int) $settings->getDownloadable()],
            'def_access' => ['integer', $settings->getDefaultAccess()],
            'sortmode' => ['integer', $settings->getSortMode()],
            'viewmode' => ['text', $settings->getViewMode()],
            'autoplaymode' => ['integer', (int) $settings->getAutoplayMode()],
            'nr_initial_videos' => ['integer', $settings->getNumberInitialVideos()],
            'new_items_in_lp' => ['integer', (int) $settings->getNewItemsInLearningProgress()],
        ], [
            'id' => ['integer', $settings->getId()],
        ]);
    }

    public function create(Settings $settings): void
    {
        $this->db->insert('il_media_cast_data', [
            'id' => ['integer', $settings->getId()],
            'public_files' => ['integer', (int) $settings->getPublicFiles()],
            'downloadable' => ['integer', (int) $settings->getDownloadable()],
            'def_access' => ['integer', $settings->getDefaultAccess()],
            'sortmode' => ['integer', $settings->getSortMode()],
            'viewmode' => ['text', $settings->getViewMode()],
            'autoplaymode' => ['integer', (int) $settings->getAutoplayMode()],
            'nr_initial_videos' => ['integer', $settings->getNumberInitialVideos()],
            'new_items_in_lp' => ['integer', (int) $settings->getNewItemsInLearningProgress()],
        ]);
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            "DELETE FROM il_media_cast_data WHERE id = %s",
            ["integer"],
            [$id]
        );
    }

    protected function getSettingsFromRecord(array $record): Settings
    {
        return $this->data->settings(
            (int) $record['id'],
            (bool) $record['public_files'],
            (bool) $record['downloadable'],
            (int) $record['def_access'],
            (int) $record['sortmode'],
            (string) $record['viewmode'],
            (bool) $record['autoplaymode'],
            (int) $record['nr_initial_videos'],
            (bool) $record['new_items_in_lp']
        );
    }
}
