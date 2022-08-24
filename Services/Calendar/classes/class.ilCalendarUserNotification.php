<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCalendarUserNotification
{
    public const TYPE_USER = 1;
    public const TYPE_EMAIL = 2;

    private int $cal_id = 0;
    private array $rcps = array();

    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilErrorHandling $error;

    public function __construct(int $a_cal_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->error = $DIC['ilErr'];

        $this->cal_id = $a_cal_id;
        $this->read();
    }

    public static function deleteUser(int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'DELETE FROM cal_notification ' .
            'WHERE user_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public static function deleteCalendarEntry(int $a_cal_id): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = 'DELETE FROM cal_notification ' .
            'WHERE cal_id = ' . $ilDB->quote($a_cal_id, 'integer');
        $res = $ilDB->manipulate($query);
    }

    public function setEntryId(int $a_id): void
    {
        $this->cal_id = $a_id;
    }

    public function getEntryId(): int
    {
        return $this->cal_id;
    }

    public function getRecipients(): array
    {
        return $this->rcps;
    }

    public function validate(): bool
    {
        if (!count($this->getRecipients())) {
            return true;
        }
        foreach ($this->getRecipients() as $rcp_data) {
            if ($rcp_data['type'] == self::TYPE_USER) {
                continue;
            } elseif (!ilUtil::is_email($rcp_data['email'])) {
                $this->error->appendMessage($this->lng->txt('cal_err_invalid_notification_rcps'));
                return false;
            }
        }
        return true;
    }

    public function save(): bool
    {
        $this->deleteRecipients();
        foreach ($this->getRecipients() as $rcp) {
            $query = 'INSERT INTO cal_notification ' .
                '(notification_id,cal_id, user_type, user_id, email) ' .
                'VALUES ( ' .
                $this->db->quote($this->db->nextId('cal_notification'), 'integer') . ', ' .
                $this->db->quote($this->getEntryId(), 'integer') . ', ' .
                $this->db->quote((int) $rcp['type'], 'integer') . ', ' .
                $this->db->quote((int) $rcp['usr_id'], 'integer') . ', ' .
                $this->db->quote($rcp['email'], 'text') .
                ')';
            $this->db->manipulate($query);
        }
        return true;
    }

    public function addRecipient(int $a_type, int $a_usr_id = 0, string $a_email = ''): void
    {
        $this->rcps[] = array(
            'type' => $a_type,
            'usr_id' => $a_usr_id,
            'email' => $a_email
        );
    }

    public function setRecipients(array $a_rcps): void
    {
        $this->rcps = array();
    }

    public function deleteRecipients(): void
    {
        $query = 'DELETE FROM cal_notification ' .
            'WHERE cal_id = ' . $this->db->quote($this->getEntryId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    protected function read(): void
    {
        if (!$this->getEntryId()) {
            return;
        }

        $query = 'SELECT * FROM cal_notification ' .
            'WHERE cal_id = ' . $this->db->quote($this->getEntryId(), 'integer');
        $res = $this->db->query($query);

        $this->rcps = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->addRecipient(
                (int) $row->user_type,
                (int) $row->user_id,
                $row->email
            );
        }
    }

    public static function createTable(): void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        if ($ilDB->tableExists('cal_notification')) {
            return;
        }

        // Create notification table
        $ilDB->createTable(
            'cal_notification',
            array(
                'notification_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true),
                'cal_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
                'user_type' => array('type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0),
                'user_id' => array('type' => 'integer', 'length' => 4, 'notnull' => true, 'default' => 0),
                'email' => array('type' => 'text', 'length' => 64, 'notnull' => false)
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
