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

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilUserFilter
{
    private static ?ilUserFilter $instance = null;
    private array $folder_ids = array(); // Missing array type.

    protected function __construct()
    {
        $this->init();
    }

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilUserFilter();
    }

    /**
     * Filter user accounts
     */
    public function filter(array $a_user_ids): array // Missing array type.
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            return $a_user_ids;
        }

        $query = "SELECT usr_id FROM usr_data " .
            "WHERE " . $ilDB->in('time_limit_owner', $this->folder_ids, false, 'integer') . " " .
            "AND " . $ilDB->in('usr_id', $a_user_ids, false, 'integer');
        $res = $ilDB->query($query);

        $filtered = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $filtered[] = $row['usr_id'];
        }
        return $filtered;
    }

    public function getFolderIds(): array // Missing array type.
    {
        return $this->folder_ids;
    }

    private function init(): void
    {
        if (ilUserAccountSettings::getInstance()->isUserAccessRestricted()) {
            $this->folder_ids = ilLocalUser::_getFolderIds();
        }
    }
}
