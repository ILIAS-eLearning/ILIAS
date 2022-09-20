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

namespace ILIAS\Awareness;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class AwarenessSessionRepository
{
    public const KEY_BASE = "awrn_";

    public function __construct()
    {
    }

    public function setOnlineUsersTS(string $ts): void
    {
        \ilSession::set(self::KEY_BASE . "_online_users_ts", $ts);
    }

    public function getOnlineUsersTS(): string
    {
        if (\ilSession::has(self::KEY_BASE . "_online_users_ts")) {
            return \ilSession::get(self::KEY_BASE . "_online_users_ts");
        }
        return "";
    }

    public function setLastUpdate(int $val): void
    {
        $key = self::KEY_BASE . "last_update";
        \ilSession::set($key, (string) $val);
    }

    public function getLastUpdate(): int
    {
        $key = self::KEY_BASE . "last_update";
        if (\ilSession::has($key)) {
            return (int) \ilSession::get($key);
        }
        return 0;
    }

    public function setCount(int $val): void
    {
        $key = self::KEY_BASE . "cnt";
        \ilSession::set($key, (string) $val);
    }

    public function getCount(): int
    {
        $key = self::KEY_BASE . "cnt";
        if (\ilSession::has($key)) {
            return (int) \ilSession::get($key);
        }
        return 0;
    }

    public function setHighlightCount(int $val): void
    {
        $key = self::KEY_BASE . "hcnt";
        \ilSession::set($key, (string) $val);
    }

    public function getHighlightCount(): int
    {
        $key = self::KEY_BASE . "hcnt";
        if (\ilSession::has($key)) {
            return (int) \ilSession::get($key);
        }
        return 0;
    }
}
