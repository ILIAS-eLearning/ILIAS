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
 * All course contacts listed
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderCourseContacts implements Provider
{
    protected \ilObjUser $user;
    protected \ilLanguage $lng;
    protected \ilDBInterface $db;

    public function __construct(Container $DIC)
    {
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    /**
     * Get provider id
     * @return string provider id
     */
    public function getProviderId(): string
    {
        return "crs_contacts";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle(): string
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_support_contacts");
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo(): string
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_support_contacts_info");
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null): array
    {
        $ub = array();
        $support_contacts = \ilParticipants::_getAllSupportContactsOfUser($this->user->getId(), "crs");
        foreach ($support_contacts as $c) {
            $ub[] = (int) $c["usr_id"];
        }
        return $ub;
    }

    public function isHighlighted(): bool
    {
        return false;
    }
}
