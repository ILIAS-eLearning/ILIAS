<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Handles calendar authentication tokens for external calendar subscriptions
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarAuthenticationToken
{
    public const SELECTION_NONE = 0;
    public const SELECTION_PD = 1;
    public const SELECTION_CATEGORY = 2;
    public const SELECTION_CALENDAR = 3;

    private int $user;

    private string $token = '';
    private int $selection_type = self::SELECTION_NONE;
    private int $calendar = 0;

    private ?string $ical = null;
    private int $ical_ctime = 0;

    protected ilDBInterface $db;

    public function __construct(int $a_user_id, string $a_token = '')
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->user = $a_user_id;
        $this->token = $a_token;
        $this->read();
    }

    public static function lookupAuthToken(int $a_user_id, int $a_selection, int $a_calendar = 0): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM cal_auth_token " .
            "WHERE user_id = " . $ilDB->quote($a_user_id, 'integer') . ' ' .
            "AND selection = " . $ilDB->quote($a_selection, 'integer') . ' ' .
            "AND calendar = " . $ilDB->quote($a_calendar, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->hash;
        }
        return '';
    }

    public static function lookupUser(string $a_token): int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM cal_auth_token " .
            "WHERE hash = " . $ilDB->quote($a_token, 'text');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->user_id;
        }
        return 0;
    }

    public function getSelectionType(): int
    {
        return $this->selection_type;
    }

    public function getUserId(): int
    {
        return $this->user;
    }

    public function setSelectionType(int $a_type): void
    {
        $this->selection_type = $a_type;
    }

    public function setCalendar(int $a_cal): void
    {
        $this->calendar = $a_cal;
    }

    public function getCalendar(): int
    {
        return $this->calendar;
    }

    public function setIcal(string $ical): void
    {
        $this->ical = $ical;
    }

    public function getIcal(): string
    {
        return $this->ical;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function storeIcal(): void
    {
        $this->db->update(
            'cal_auth_token',
            array(
                'ical' => array('clob', $this->getIcal()),
                'c_time' => array('integer', time())
            ),
            array(
                'user_id' => array('integer', $this->getUserId()),
                'hash' => array('text', $this->getToken())
            )
        );
    }

    /**
     * Check if cache is disabled or expired
     * @todo enable the cache
     */
    public function isIcalExpired(): bool
    {
        return true;
    }

    public function add(): string
    {
        $this->createToken();
        $query = "INSERT INTO cal_auth_token (user_id,hash,selection,calendar) " .
            "VALUES ( " .
            $this->db->quote($this->getUserId(), 'integer') . ', ' .
            $this->db->quote($this->getToken(), 'text') . ', ' .
            $this->db->quote($this->getSelectionType(), 'integer') . ', ' .
            $this->db->quote($this->getCalendar(), 'integer') . ' ' .
            ')';
        $this->db->manipulate($query);
        return $this->getToken();
    }

    protected function createToken(): void
    {
        $random = new \ilRandom();
        $this->token = md5($this->getUserId() . $this->getSelectionType() . $random->int());
    }

    protected function read(): bool
    {
        if (!$this->getToken()) {
            $query = "SELECT * FROM cal_auth_token " .
                "WHERE user_id = " . $this->db->quote($this->getUserId(), 'integer');
        } else {
            $query = 'SELECT * FROM cal_auth_token ' .
                'WHERE user_id = ' . $this->db->quote($this->getUserId(), 'integer') . ' ' .
                'AND hash = ' . $this->db->quote($this->getToken(), 'text');
        }

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->token = $row->hash;
            $this->selection_type = (int) $row->selection;
            $this->calendar = (int) $row->calendar;
            $this->ical = $row->ical;
            $this->ical_ctime = (int) $row->c_time;
        }
        return true;
    }
}
