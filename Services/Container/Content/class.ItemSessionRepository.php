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

namespace ILIAS\Container\Content;

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemSessionRepository
{
    protected const KEY_BASE = "cont_item";

    public function __construct()
    {
    }

    public function setExpanded(int $id, int $val): void
    {
        \ilSession::set(self::KEY_BASE . "_" . $id . "_expanded", $val);
    }

    public function getExpanded(int $id): ?int
    {
        if (\ilSession::has(self::KEY_BASE . "_" . $id . "_expanded")) {
            return \ilSession::get(self::KEY_BASE . "_" . $id . "_expanded");
        }
        return null;
    }
}
