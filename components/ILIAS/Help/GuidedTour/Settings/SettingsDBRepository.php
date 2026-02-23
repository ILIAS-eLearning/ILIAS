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

namespace ILIAS\Help\GuidedTour\Settings;

use ilDBInterface;
use ILIAS\Help\GuidedTour\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function save(Settings $settings): void
    {
        $this->db->replace('help_gt_settings', [
            'obj_id' => ['integer', $settings->getObjId()]
        ], [
            'active' => ['integer', $settings->isActive() ? 1 : 0],
            'screen_ids' => ['text', $settings->getScreenIds()],
            'permission' => ['integer', $settings->getPermission()->value],
            'lang' => ['text', $settings->getLanguage()]
        ]);
    }

    public function getByObjId(int $obj_id): ?Settings
    {
        $set = $this->db->queryF(
            'SELECT * FROM help_gt_settings WHERE obj_id = %s',
            ['integer'],
            [$obj_id]
        );

        $record = $this->db->fetchAssoc($set);
        if (!$record) {
            return null;
        }

        return $this->data->settings(
            (int) $record['obj_id'],
            (bool) $record['active'],
            (string) $record['screen_ids'],
            PermissionType::from((int) $record['permission']),
            (string) $record['lang'],
        );
    }

    public function delete(int $obj_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM help_gt_settings WHERE obj_id = %s',
            ['integer'],
            [$obj_id]
        );
    }
}
