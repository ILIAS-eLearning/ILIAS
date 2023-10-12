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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\User\Profile;

class ProfileChangeMailTokenDBRepository implements ProfileChangeMailTokenRepository
{
    private const TABLE_NAME = 'usr_change_email_token';
    private const VALIDITY = 300;
    private $db;

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->deleteExpiredEntries();
    }

    public function getNewTokenForUser(\ilObjUser $user, string $new_email) : string
    {
        $token = hash('md5', $user->getId() . '-' . $user->getEmail());
        $result = $this->db->replace(
            self::TABLE_NAME,
            [
                'token' => ['text', $token]
            ],
            [
                'new_email' => [\ilDBConstants::T_TEXT, $new_email],
                'valid_until' => [\ilDBConstants::T_INTEGER, time() + self::VALIDITY]
            ]
        );

        if ($result === 1) {
            return $token;
        }

        return '';
    }

    public function getNewEmailForUser(\ilObjUser $user, string $received_token) : string
    {
        if (hash('md5', $user->getId() . '-' . $user->getEmail()) !== $received_token) {
            return '';
        }

        $query = $this->db->queryF(
            'SELECT `new_email` FROM `' . self::TABLE_NAME . '` WHERE `token` = %s AND `valid_until` >= %s',
            [\ilDBConstants::T_TEXT, \ilDBConstants::T_INTEGER],
            [$received_token, time()]
        );

        $result = $this->db->fetchObject($query);

        if ($result !== null) {
            return $result->new_email;
        }

        return '';
    }

    public function deleteEntryByToken(string $token) : void
    {
        $query = 'DELETE FROM `' . self::TABLE_NAME . '` WHERE `token` = %s';
        $this->db->manipulateF($query, [\ilDBConstants::T_TEXT], [$token]);
    }

    private function deleteExpiredEntries() : void
    {
        $query = 'DELETE FROM `' . self::TABLE_NAME . '` WHERE `valid_until` <= %s';
        $this->db->manipulateF($query, [\ilDBConstants::T_INTEGER], [time()]);
    }
}
