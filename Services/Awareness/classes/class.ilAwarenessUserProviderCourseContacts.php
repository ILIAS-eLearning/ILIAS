<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * All course contacts listed
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAwarenessUserProviderCourseContacts extends ilAwarenessUserProvider
{
    /**
     * Get provider id
     *
     * @return string provider id
     */
    public function getProviderId()
    {
        return "crs_contacts";
    }

    /**
     * Provider title (used in awareness overlay and in administration settings)
     *
     * @return string provider title
     */
    public function getTitle()
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_support_contacts");
    }

    /**
     * Provider info (used in administration settings)
     *
     * @return string provider info text
     */
    public function getInfo()
    {
        $this->lng->loadLanguageModule("crs");
        return $this->lng->txt("crs_awrn_support_contacts_info");
    }

    /**
     * Get initial set of users
     *
     * @return array array of user IDs
     */
    public function getInitialUserSet()
    {
        $ub = array();
        $support_contacts = ilParticipants::_getAllSupportContactsOfUser($this->getUserId(), "crs");
        foreach ($support_contacts as $c) {
            $ub[] = $c["usr_id"];
        }
        return $ub;
    }
}
