<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceHelper.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilObjTermsOfService extends ilObject2
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilSetting
     */
    protected $settings;
    
    /**
     * @param int  $a_id
     * @param bool $a_reference
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_reference);

        $this->db       = $DIC['ilDB'];
        $this->settings = $DIC['ilSetting'];
    }

    /**
     *
     */
    protected function initType()
    {
        $this->type = 'tos';
    }

    /**
     *
     */
    public function resetAll()
    {
        $in = $this->db->in('usr_id', array(ANONYMOUS_USER_ID, SYSTEM_USER_ID), true, 'integer');
        $this->db->manipulate("UPDATE usr_data SET agree_date = NULL WHERE $in");

        $this->settings->set('tos_last_reset', time());
    }

    /**
     * @return ilDateTime
     */
    public function getLastResetDate()
    {
        return new ilDateTime($this->settings->get('tos_last_reset'), IL_CAL_UNIX);
    }

    /**
     * @param bool $status
     */
    public function saveStatus($status)
    {
        ilTermsOfServiceHelper::setStatus((bool) $status);
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return ilTermsOfServiceHelper::isEnabled();
    }
}
