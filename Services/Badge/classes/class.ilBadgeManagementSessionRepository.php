<?php declare(strict_types = 1);

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
 * Stores repository clipboard data
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBadgeManagementSessionRepository
{
    public const KEY = "bdgclpbrd";

    public function __construct()
    {
    }

    public function setBadgeIds(array $ids) : void
    {
        \ilSession::set(self::KEY, $ids);
    }

    public function getBadgeIds() : array
    {
        if (\ilSession::has(self::KEY)) {
            return \ilSession::get(self::KEY);
        }
        return [];
    }

    public function clear() : void
    {
        \ilSession::clear(self::KEY);
    }
}
