<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAwarenessUserProviderContactRequests
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderContactRequests extends ilAwarenessUserProvider
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
        return 'contact_approved';
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts');
    }

    /**
     * @inheritDoc
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts_info');
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

        return ilBuddyList::getInstanceByGlobalUser()->getRequestRelationsForOwner()->getKeys();
    }

    /**
     * @inheritDoc
     */
    public function isHighlighted()
    {
        return true;
    }
}