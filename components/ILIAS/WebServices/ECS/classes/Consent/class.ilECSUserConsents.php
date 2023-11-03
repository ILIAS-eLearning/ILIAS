<?php

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
 */

declare(strict_types=1);

/**
 * Class ilECSUserConsents
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSUserConsents
{
    /**
     * @var array<int, self>
     */
    private static array $instances = [];

    private int $usr_id;
    /**
     * @var array<int, ilECSUserConsent>
     */
    private array $consents = [];

    protected ilDBInterface $db;

    protected function __construct(int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->usr_id = $a_usr_id;
        if ($a_usr_id > 0) {
            $this->read();
        }
    }

    public static function getInstanceByUserId(int $a_usr_id): self
    {
        if (!isset(self::$instances[$a_usr_id])) {
            self::$instances[$a_usr_id] = new self($a_usr_id);
        }
        return self::$instances[$a_usr_id];
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function hasConsented(int $server_id, int $a_mid): bool
    {
        return array_key_exists("{$server_id}:{$a_mid}", $this->consents);
    }

    public function delete(): void
    {
        foreach ($this->consents as $mid => $consent) {
            $consent->delete();
        }
        $this->consents = [];
    }

    public function add(int $server_id, int $a_mid): void
    {
        if (!$this->hasConsented($server_id, $a_mid)) {
            $consent = new ilECSUserConsent($this->getUserId(), $server_id, $a_mid);
            $consent->save();
            $this->consents["{$server_id}:{$a_mid}"] = $consent;
        }
    }

    protected function read(): void
    {
        $query = 'SELECT * FROM ecs_user_consent ' .
            'WHERE usr_id = ' . $this->db->quote(
                $this->getUserId(),
                ilDBConstants::T_INTEGER
            );
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->consents["{$row->server_id}:{$row->usr_id}"] = new ilECSUserConsent(
                (int) $row->usr_id,
                (int) $row->server_id,
                (int) $row->mid
            );
        }
    }
}
