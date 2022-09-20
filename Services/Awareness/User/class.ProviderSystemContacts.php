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
 * All system contacts listed
 * @author Alexander Killing <killing@leifos.de>
 */
class ProviderSystemContacts implements Provider
{
    protected \ilDBInterface $db;
    protected \ilLanguage $lng;

    public function __construct(Container $DIC)
    {
        $this->db = $DIC->database();
        $this->lng = $DIC->language();
    }

    public function getProviderId(): string
    {
        return "adm_contacts";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle(): string
    {
        $this->lng->loadLanguageModule("adm");
        return $this->lng->txt("adm_support_contacts");
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo(): string
    {
        $this->lng->loadLanguageModule("adm");
        return $this->lng->txt("adm_awrn_support_contacts_info");
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null): array
    {
        return \ilSystemSupportContacts::getValidSupportContactIds();
    }

    public function isHighlighted(): bool
    {
        return false;
    }
}
