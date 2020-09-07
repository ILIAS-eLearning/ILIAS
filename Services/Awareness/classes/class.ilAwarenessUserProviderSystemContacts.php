<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All system contacts listed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderSystemContacts extends ilAwarenessUserProvider
{
    /**
     * Get provider id
     *
     * @return string provider id
     */
    public function getProviderId()
    {
        return "adm_contacts";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     *
     * @return string provider title
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule("adm");
        return $this->lng->txt("adm_support_contacts");
    }

    /**
     * Provider info (used in administration settings)
     *
     * @return string provider info text
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule("adm");
        return $this->lng->txt("adm_awrn_support_contacts_info");
    }

    /**
     * Get initial set of users
     *
     * @return array array of user IDs
     */
    public function getInitialUserSet()
    {
        include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
        return ilSystemSupportContacts::getValidSupportContactIds();
    }
}
