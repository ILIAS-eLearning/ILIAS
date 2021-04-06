<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * All system contacts listed
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
        return ilSystemSupportContacts::getValidSupportContactIds();
    }
}
