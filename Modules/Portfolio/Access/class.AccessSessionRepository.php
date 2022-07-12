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

namespace ILIAS\Portfolio\Access;

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AccessSessionRepository
{
    public const KEY_BASE = "port_acc_";

    public function __construct()
    {
    }

    public function setSharedSessionPassword(int $node_id, string $pw) : void
    {
        $key = self::KEY_BASE . "_shpw_" . $node_id;
        \ilSession::set($key, $pw);
    }

    public function getSharedSessionPassword(int $node_id) : string
    {
        $key = self::KEY_BASE . "_shpw_" . $node_id;
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return "";
    }
}
