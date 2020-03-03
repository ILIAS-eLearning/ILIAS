<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Awareness/classes/class.ilAwarenessUserProvider.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderContactRequests extends ilAwarenessUserProvider
{
    /**
     * @var ilObjUser
     */
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
     * Get provider id
     * @return string provider id
     */
    public function getProviderId()
    {
        return 'contact_approved';
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     * @return string provider title
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts');
    }

    /**
     * Provider info (used in administration settings)
     * @return string provider info text
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule('contact');
        return $this->lng->txt('contact_awrn_req_contacts_info');
    }

    /**
     * Get initial set of users
     * @return array array of user IDs
     */
    public function getInitialUserSet()
    {
        if ($this->user->isAnonymous()) {
            return array();
        }

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddySystem.php';
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return array();
        }

        require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
        $buddylist = ilBuddyList::getInstanceByGlobalUser();
        return $buddylist->getRequestRelationsForOwner()->getKeys();
    }

    /**
     * Is highlighted
     *
     * @return bool return true, if user group should be highlighted (using extra highlighted number)
     */
    public function isHighlighted()
    {
        return true;
    }
}
