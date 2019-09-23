<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAwarenessUserProviderApprovedContacts
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderApprovedContacts extends ilAwarenessUserProvider
{
    /** @var ilObjUser */
    protected $user;

    /**
     * ilAwarenessUserProviderApprovedContacts constructor.
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->user = $DIC['ilUser'];
    }

    /**
     * @inheritDoc
     */
    public function getProviderId()
    {
        return 'contact_requests';
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_ap_contacts');
    }

    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_ap_contacts_info');
    }

    /**
     * @inheritDoc
     */
    public function getInitialUserSet()
    {
        if ($this->user->isAnonymous()) {
            return [];
        }

        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return [];
        }

        return ilBuddyList::getInstanceByGlobalUser()->getLinkedRelations()->getKeys();
    }
}