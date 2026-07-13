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

namespace ILIAS\User\Profile\ChangeMail;

class DBRepository implements Repository
{
    public const TABLE_NAME = 'usr_change_email_token';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly \ilSetting $settings
    ) {
    }

    public function getNewTokenForUser(
        \ilObjUser $user,
        string $new_email,
        int $now
    ): Token {
        $token = new Token(
            $user->getId(),
            $user->getEmail(),
            $new_email,
            $now
        );

        $this->storeToken($token);
        return $token;
    }

    public function hasUserValidEmailConfirmationToken(\ilObjUser $user): bool
    {
        $query = $this->db->queryF(
            'SELECT count(*) as cnt FROM `' . self::TABLE_NAME . '`' . PHP_EOL
                . 'WHERE `usr_id` = %s' . PHP_EOL
                . 'AND `status` = %s' . PHP_EOL
                . 'AND `created_ts` >= %s',
            [
                \ilDBConstants::T_INTEGER,
                \ilDBConstants::T_INTEGER,
                \ilDBConstants::T_INTEGER
            ],
            [
                $user->getId(),
                Status::EmailConfirmation->value,
                time() - Status::EmailConfirmation->getValidity($this->settings)
            ]
        );

        $result = $this->db->fetchObject($query);

        if ($result->cnt > 0) {
            return true;
        }

        return false;
    }

    public function getTokenForTokenString(string $token_string, \ilObjUser $user): ?Token
    {
        $query = $this->db->queryF(
            'SELECT * FROM `' . self::TABLE_NAME . '` WHERE `token` = %s',
            [\ilDBConstants::T_TEXT],
            [$token_string]
        );

        $result = $this->db->fetchObject($query);

        if ($result === null) {
            return null;
        }

        $token = new Token(
            $user->getId(),
            $user->getEmail(),
            $result->new_email,
            $result->created_ts,
            Status::from($result->status),
            $result->token
        );

        if (!$token->isTokenValidForCurrentStatus($this->settings)) {
            return null;
        }

        return $token;
    }

    public function moveToNextStep(Token $token, int $now): Token
    {
        $new_token = new Token(
            $token->getUserId(),
            $token->getCurrentEmail(),
            $token->getNewEmail(),
            $now,
            $token->getStatus()->next()
        );
        $this->deleteEntryByToken($token->getToken());
        $this->storeToken($new_token);
        return $new_token;
    }

    public function deleteEntryByToken(string $token): void
    {
        $query = 'DELETE FROM `' . self::TABLE_NAME . '` WHERE `token` = %s';
        $this->db->manipulateF($query, [\ilDBConstants::T_TEXT], [$token]);
    }

    public function deleteExpiredEntries(): void
    {
        $validity = max(
            Status::Login->getValidity($this->settings),
            Status::EmailConfirmation->getValidity($this->settings)
        );
        $query = 'DELETE FROM `' . self::TABLE_NAME . '` WHERE `created_ts` < %s';
        $this->db->manipulateF($query, [\ilDBConstants::T_INTEGER], [time() - $validity]);
    }

    private function storeToken(Token $token): void
    {
        $this->db->replace(
            self::TABLE_NAME,
            [
                'token' => [\ilDBConstants::T_TEXT, $token->getToken()]
            ],
            [
                'usr_id' => [\ilDBConstants::T_TEXT, $token->getUserId()],
                'new_email' => [\ilDBConstants::T_TEXT, $token->getNewEmail()],
                'status' => [\ilDBConstants::T_INTEGER, $token->getStatus()->value],
                'created_ts' => [\ilDBConstants::T_INTEGER, $token->getCreatedTimestamp()]
            ]
        );
    }
}
