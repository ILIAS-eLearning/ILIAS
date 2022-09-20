<?php

declare(strict_types=1);

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

use ILIAS\Awareness\User\Provider;
use ILIAS\DI\Container;

/**
 * Class ilAwarenessUserProviderContactRequests
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderContactRequests implements Provider
{
    protected ilObjUser $user;
    protected ilLanguage $lng;

    public function __construct(Container $DIC)
    {
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
    }

    /**
     * @inheritDoc
     */
    public function getProviderId(): string
    {
        return 'contact_approved';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts');
    }

    /**
     * @inheritDoc
     */
    public function getInfo(): string
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts_info');
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null): array
    {
        if ($this->user->isAnonymous()) {
            return [];
        }

        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return [];
        }

        return ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->getKeys();
    }

    /**
     * @inheritDoc
     */
    public function isHighlighted(): bool
    {
        return true;
    }
}
