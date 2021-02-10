<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Dashboard\Access;

/**
 * Dashboard permission wrapper
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardAccess
{
    /**
     * @var \ilRbacSystem
     */
    protected $rbac_system;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var int
     */
    protected static $setting_ref_id = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->rbac_system = $DIC->rbac()->system();
    }

    /**
     * Get dashboard settings ref id
     */
    protected function getSettingsRefId(): int
    {
        if (self::$setting_ref_id == 0) {
            $set = $this->db->queryF(
                'SELECT object_reference.ref_id FROM object_reference, tree, object_data
                WHERE tree.parent = %s
                AND object_data.type = %s
                AND object_reference.ref_id = tree.child
                AND object_reference.obj_id = object_data.obj_id',
                array('integer', 'text'),
                array(SYSTEM_FOLDER_ID, 'dshs')
            );
            $rec = $this->db->fetchAssoc($set);
            self::$setting_ref_id = (int) $rec["ref_id"];
        }
        return self::$setting_ref_id;
    }

    /**
     * @param int $user_id
     */
    public function canChangePresentation(int $user_id): bool {
        return $this->rbac_system->checkAccessOfUser($user_id, "change_presentation", $this->getSettingsRefId());
    }
}