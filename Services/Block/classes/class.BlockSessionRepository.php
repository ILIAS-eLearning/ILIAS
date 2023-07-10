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

namespace ILIAS\Block;

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class BlockSessionRepository
{
    public const KEY_BASE = "block_";

    public function __construct()
    {
    }

    public function setNavPar(
        string $par,
        string $val
    ): void {
        \ilSession::set(self::KEY_BASE . $par, $val);
    }

    public function getNavPar(string $par): string
    {
        if (\ilSession::has(self::KEY_BASE . $par)) {
            return \ilSession::get(self::KEY_BASE . $par);
        }
        return "";
    }
}
