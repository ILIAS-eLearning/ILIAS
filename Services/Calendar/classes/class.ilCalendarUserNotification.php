<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilCalendarUserNotification
{
    const TYPE_USER = 1;
    const TYPE_EMAIL = 2;


    private $cal_id = 0;
    private $rcps = array();

    /**
     * Init with calendar entry id
     */
    public function __construct($a_cal_id = 0)
    {
        $this->cal_id = $a_cal_id;
        $this->read();
    }

    /**
     * Delete a singel user
     * @global ilDB $ilDB
     * @param int $a_usr_id
     * @return bool
     */
    public static function deleteUser($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM cal_notification ' .
            'WHERE user_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Delete notification for a calendar entry
     * @global ilDB $ilDB
     * @param int $a_cal_id
     * @return bool
     */
    public static function deleteCalendarEntry($a_cal_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM cal_notification ' .
            'WHERE cal_id = ' . $ilDB->quote($a_cal_id, 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }

    /**
     * Set calendar entry id
     * @param int $a_id
     */
    public function setEntryId($a_id)
    {
        $this->cal_id = $a_id;
    }

    /**
     * Get calendar entry id
     */
    public function getEntryId()
    {
        return $this->cal_id;
    }

    public function getRecipients()
    {
        return (array) $this->rcps;
    }

    public function validate()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        if (!count($this->getRecipients())) {
            return true;
        }
        foreach ((array) $this->getRecipients() as $rcp_data) {
            if ($rcp_data['type'] == self::TYPE_USER) {
                continue;
            } else {
                if (!ilUtil::is_email($rcp_data['email'])) {
                    $ilErr->appendMessage($lng->txt('cal_err_invalid_notification_rcps'));
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Save recipients to db
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->deleteRecipients();

        foreach ($this->getRecipients() as $rcp) {
            $query = 'INSERT INTO cal_notification ' .
                '(notification_id,cal_id, user_type, user_id, email) ' .
                'VALUES ( ' .
                $ilDB->quote($ilDB->nextId('cal_notification'), 'integer') . ', ' .
                $ilDB->quote((int) $this->getEntryId(), 'integer') . ', ' .
                $ilDB->quote((int) $rcp['type'], 'integer') . ', ' .
                $ilDB->quote((int) $rcp['usr_id'], 'integer') . ', ' .
                $ilDB->quote($rcp['email'], 'text') .
                ')';
            $ilDB->manipulate($query);
        }
        return true;
    }

    /**
     * Add recipient
     * @param int $a_type
     * @param int $a_usr_id
     * @param string $a_email
     */
    public function addRecipient($a_type, $a_usr_id = 0, $a_email = '')
    {
        $this->rcps[] = array(
            'type' => $a_type,
            'usr_id' => $a_usr_id,
            'email' => $a_email
        );
    }

    /**
     * Set recipients
     * @param array $a_rcps
     */
    public function setRecipients($a_rcps)
    {
        $this->rcps = array();
    }

    /**
     * Delete all recipients
     * @global ilDB $ilDB
     * @return bool
     */
    public function deleteRecipients()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'DELETE FROM cal_notification ' .
            'WHERE cal_id = ' . $ilDB->quote($this->getEntryId(), 'integer');
        $res = $ilDB->manipulate($query);
        return true;
    }



    /**
     * Read recipients
     * @global ilDB $ilDB
     */
    protected function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->getEntryId()) {
            return true;
        }

        $query = 'SELECT * FROM cal_notification ' .
            'WHERE cal_id = ' . $ilDB->quote($this->getEntryId(), 'integer');
        $res = $ilDB->query($query);

        $this->rcps = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->addRecipient(
                $row->user_type,
                $row->user_id,
                $row->email
            );
        }
    }

    // Create table (not merged into into 4.3)
    public static function createTable()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($ilDB->tableExists('cal_notification')) {
            return true;
        }

        // Create notification table
        $ilDB->createTable(
            'cal_notification',
            array(
                'notification_id' => array('type' => 'integer','length' => 4,'notnull' => true),
                'cal_id' => array('type' => 'integer','length' => 4, 'notnull' => true, 'default' => 0),
                'user_type' => array('type' => 'integer','length' => 1, 'notnull' => true, 'default' => 0),
                'user_id' => array('type' => 'integer','length' => 4, 'notnull' => true, 'default' => 0),
                'email' => array('type' => 'text','length' => 64, 'notnull' => false)
            )
        );
        $ilDB->addPrimaryKey(
            'cal_notification',
            array(
                'notification_id'
            )
        );
        $ilDB->createSequence('cal_notification');
        $ilDB->addIndex('cal_notification', array('cal_id'), 'i1');
    }
}
