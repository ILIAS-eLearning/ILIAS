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

namespace ILIAS\Awareness\User;

use ILIAS\DI\Container;

/**
 * Test provider, adds all users
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderAllUsers implements Provider
{
    protected \ilLanguage $lng;
    protected \ilDBInterface $db;

    public function __construct(Container $DIC)
    {
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
    }

    public function getProviderId(): string
    {
        return "user_all";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle(): string
    {
        $this->lng->loadLanguageModule("user");
        return $this->lng->txt("user_awrn_all_users");
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo(): string
    {
        $this->lng->loadLanguageModule("user");
        return $this->lng->txt("user_awrn_all_users_info");
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null): array
    {
        $ilDB = $this->db;

        $ub = array();
        // all online users
        if (!is_null($user_ids)) {
            return $user_ids;
        } else {	// all users
            $set = $ilDB->query("SELECT usr_id FROM usr_data ");
            while ($rec = $ilDB->fetchAssoc($set)) {
                $ub[] = (int) $rec["usr_id"];
            }
        }
        return $ub;
    }

    public function isHighlighted(): bool
    {
        return false;
    }
}
