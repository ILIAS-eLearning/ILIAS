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
 * Stores block properties
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BlockSessionRepository
{
    protected const KEY_BASE = "cont_block";

    public function __construct()
    {
    }

    public function setProperty(
        string $a_block_id,
        int $a_user_id,
        string $a_property,
        string $a_value
    ): void {
        \ilSession::set(self::KEY_BASE . "_" .
            $a_block_id . "_" . $a_user_id . "_" . $a_property, $a_value);
    }

    public function getProperty(
        string $a_block_id,
        int $a_user_id,
        string $a_property
    ): string {
        $key = self::KEY_BASE . "_" . $a_block_id . "_" . $a_user_id . "_" . $a_property;
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return "";
    }
}
