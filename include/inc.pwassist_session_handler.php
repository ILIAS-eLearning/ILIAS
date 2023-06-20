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
 * Creates a new secure id.
 *
 * The secure id has the following characteristics:
 * - It is unique
 * - It is a non-uniformly distributed (pseudo) random value
 * - Only a non-substantial number of bits can be predicted from
 *   previously generated id's.
 */
function db_pwassist_create_id(): string
{
    global $DIC;

    $ilDB = $DIC->database();

    do {
        $hash = bin2hex(ilPasswordUtils::getBytes(32));

        $exists = (
            (int) ($ilDB->fetchAssoc(
                $ilDB->query(
                    "SELECT EXISTS(" .
                    "SELECT 1 FROM usr_pwassist WHERE pwassist_id = " . $ilDB->quote($hash, ilDBConstants::T_TEXT) .
                    ") AS hit"
                )
            )['hit'] ?? 0) === 1
        );
    } while ($exists);

    return $hash;
}

/**
 * @return null|array{"pwassist_id": string, "expires": int|numeric-string, "user_id": int|numeric-string, "ctime": int|numeric-string}
 */
function db_pwassist_session_read(string $pwassist_id): ?array
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = 'SELECT * FROM usr_pwassist WHERE pwassist_id = ' . $ilDB->quote($pwassist_id, ilDBConstants::T_TEXT);
    $r = $ilDB->query($q);

    return $ilDB->fetchAssoc($r);
}

/**
  * @return null|array{"pwassist_id": string, "expires": int|numeric-string, "user_id": int|numeric-string, "ctime": int|numeric-string}
  */
function db_pwassist_session_find(int $user_id): ?array
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = 'SELECT * FROM usr_pwassist WHERE user_id = ' . $ilDB->quote($user_id, ilDBConstants::T_INTEGER);
    $r = $ilDB->query($q);

    return $ilDB->fetchAssoc($r);
}

function db_pwassist_session_write(string $pwassist_id, int $maxlifetime, int $user_id): void
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = 'DELETE FROM usr_pwassist ' .
        'WHERE pwassist_id = ' . $ilDB->quote($pwassist_id, ilDBConstants::T_TEXT) . ' ' .
        'OR user_id = ' . $ilDB->quote($user_id, ilDBConstants::T_INTEGER);
    $ilDB->manipulate($q);

    $ctime = time();
    $expires = $ctime + $maxlifetime;
    $ilDB->manipulateF(
        'INSERT INTO usr_pwassist (pwassist_id, expires, user_id,  ctime)  VALUES (%s, %s, %s, %s)',
        [ilDBConstants::T_TEXT, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
        [$pwassist_id, $expires, $user_id, $ctime]
    );
}

function db_pwassist_session_destroy(string $pwassist_id): void
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = 'DELETE FROM usr_pwassist WHERE pwassist_id = ' . $ilDB->quote($pwassist_id, ilDBConstants::T_TEXT);
    $ilDB->manipulate($q);
}


function db_pwassist_session_gc(): void
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = 'DELETE FROM usr_pwassist WHERE expires < ' . $ilDB->quote(time(), ilDBConstants::T_INTEGER);
    $ilDB->manipulate($q);
}
