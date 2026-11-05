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

/**
 * Keeps the pending requests of LTI Advantage in the database instead of the session: every request of ILIAS
 * writes the whole session back when it ends, so a request that ran in parallel, like the asynchronous ones
 * of the main bar, would remove a request kept in the session by another one in the meantime.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final readonly class ilLTIAdvantagePendingRequestDBStorage implements ilLTIAdvantagePendingRequestStorage
{
    public const string TABLE = 'lti_pending_requests';

    public function __construct(private ilDBInterface $db)
    {
    }

    public function read(string $session, string $kind, string $key): ?array
    {
        $result = $this->db->queryF(
            'SELECT request_data, created FROM ' . self::TABLE . ' WHERE session_id = %s AND kind = %s AND request_key = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$session, $kind, $key]
        );
        $row = $this->db->fetchAssoc($result);
        if ($row === null) {
            return null;
        }
        $request = json_decode((string) $row['request_data'], true);

        return is_array($request) ? [$request, (int) $row['created']] : null;
    }

    public function write(string $session, string $kind, string $key, array $request, int $created): void
    {
        $this->db->replace(
            self::TABLE,
            [
                'session_id' => [ilDBConstants::T_TEXT, $session],
                'kind' => [ilDBConstants::T_TEXT, $kind],
                'request_key' => [ilDBConstants::T_TEXT, $key],
            ],
            [
                'request_data' => [
                    ilDBConstants::T_CLOB,
                    json_encode($request, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)
                ],
                'created' => [ilDBConstants::T_INTEGER, $created],
            ]
        );
    }

    public function delete(string $session, string $kind, string $key): bool
    {
        return $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE session_id = %s AND kind = %s AND request_key = %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$session, $kind, $key]
        ) > 0;
    }

    public function deleteOlderThan(string $kind, int $created): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE . ' WHERE kind = %s AND created < %s',
            [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER],
            [$kind, $created]
        );
    }
}
