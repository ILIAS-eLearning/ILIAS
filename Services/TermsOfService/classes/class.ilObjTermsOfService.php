<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTermsOfService
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjTermsOfService extends \ilObject2
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilSetting
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
     * @inheritdoc
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
     * @return \ilDateTime
     * @throws ilDateTimeException
     */
    public function getLastResetDate() : \ilDateTime
    {
        return new \ilDateTime($this->settings->get('tos_last_reset'), IL_CAL_UNIX);
    }

    /**
     * @param bool $status
     */
    public function saveStatus(bool $status)
    {
        \ilTermsOfServiceHelper::setStatus((bool) $status);
    }

    /**
     * @return bool
     */
    public function getStatus() : bool
    {
        return \ilTermsOfServiceHelper::isEnabled();
    }
}
