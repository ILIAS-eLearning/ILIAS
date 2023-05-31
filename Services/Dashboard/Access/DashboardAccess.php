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

namespace ILIAS\Dashboard\Access;

use ilDBInterface;
use ILIAS\DI\Container;
use ilObjDashboardSettings;
use ilRbacSystem;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardAccess
{
    protected readonly ilRbacSystem $rbac_system;
    protected readonly ilDBInterface $db;
    private int $setting_ref_id = 0;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->rbac_system = $DIC->rbac()->system();
    }

    protected function getSettingsRefId(): int
    {
        if ($this->setting_ref_id === 0) {
            $set = $this->db->queryF(
                'SELECT object_reference.ref_id FROM object_reference, tree, object_data
                WHERE tree.parent = %s
                AND object_data.type = %s
                AND object_reference.ref_id = tree.child
                AND object_reference.obj_id = object_data.obj_id',
                ['integer', 'text'],
                [SYSTEM_FOLDER_ID, ilObjDashboardSettings::TYPE]
            );
            $rec = $this->db->fetchAssoc($set);
            $this->setting_ref_id = (int) $rec['ref_id'];
        }
        return $this->setting_ref_id;
    }

    public function canChangePresentation(int $user_id): bool
    {
        return $this->rbac_system->checkAccessOfUser($user_id, 'change_presentation', $this->getSettingsRefId());
    }
}
