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

namespace ILIAS\Portfolio\Settings;

use ilDBInterface;
use ILIAS\Portfolio\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function create(Settings $settings): void
    {
        $this->db->insert('usr_portfolio', [
            'id' => ['integer', $settings->getId()],
            'ppic' => ['integer', $settings->getShowPersonalPicture()]
        ]);
    }

    public function update(Settings $settings): void
    {
        $this->db->update('usr_portfolio', [
            'ppic' => ['integer', $settings->getShowPersonalPicture()]
        ], [
            'id' => ['integer', $settings->getId()],
        ]);
    }

    public function getById(int $id): ?Settings
    {
        $set = $this->db->queryF(
            'SELECT * FROM usr_portfolio WHERE id = %s',
            ['integer'],
            [$id]
        );
        $rec = $this->db->fetchAssoc($set);
        if ($rec) {
            return $this->getSettingsFromRecord($rec);
        }
        return null;
    }

    public function delete(int $id): void
    {
        $this->db->manipulateF(
            'DELETE FROM usr_portfolio WHERE id = %s',
            ['integer'],
            [$id]
        );
    }

    protected function getSettingsFromRecord(array $rec): Settings
    {
        return $this->data->settings(
            (int) $rec['id'],
            (bool) $rec['ppic']
        );
    }
}
