<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Awareness\User\Provider;
use ILIAS\DI\Container;

/**
 * Class ilAwarenessUserProviderApprovedContacts
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderApprovedContacts implements Provider
{
    protected ilLanguage $lng;
    protected ilObjUser $user;

    public function __construct(Container $DIC)
    {
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
    }

    /**
     * @inheritDoc
     */
    public function getProviderId() : string
    {
        return 'contact_requests';
    }

    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_ap_contacts');
    }

    /**
     * @inheritDoc
     */
    public function getInfo() : string
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_ap_contacts_info');
    }

    /**
     * Get initial set of users
     * @param ?int[] $user_ids
     * @return int[] array of user IDs
     */
    public function getInitialUserSet(?array $user_ids = null) : array
    {
        if ($this->user->isAnonymous()) {
            return [];
        }

        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return [];
        }

        return ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->getKeys();
    }

    public function isHighlighted() : bool
    {
        return false;
    }
}
