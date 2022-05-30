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

namespace ILIAS\Repository\Clipboard;

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ClipboardSessionRepository
{
    public const KEY_BASE = "repo_clip";

    public function __construct()
    {
    }

    public function setCmd(string $cmd) : void
    {
        \ilSession::set(self::KEY_BASE . "_cmd", $cmd);
    }

    public function getCmd() : string
    {
        if (\ilSession::has(self::KEY_BASE . "_cmd")) {
            return \ilSession::get(self::KEY_BASE . "_cmd");
        }
        return "";
    }

    public function setParent(int $parent) : void
    {
        \ilSession::set(self::KEY_BASE . "_parent", $parent);
    }

    public function getParent() : int
    {
        if (\ilSession::has(self::KEY_BASE . "_parent")) {
            return (int) \ilSession::get(self::KEY_BASE . "_parent");
        }
        return 0;
    }

    public function setRefIds(array $ref_ids) : void
    {
        \ilSession::set(self::KEY_BASE . "_ref_ids", $ref_ids);
    }

    public function getRefIds() : array
    {
        if (\ilSession::has(self::KEY_BASE . "_ref_ids")) {
            return \ilSession::get(self::KEY_BASE . "_ref_ids");
        }
        return [];
    }

    public function hasEntries() : bool
    {
        return (count($this->getRefIds()) > 0 && $this->getCmd() !== "");
    }

    public function clear() : void
    {
        \ilSession::clear(self::KEY_BASE . "_cmd");
        \ilSession::clear(self::KEY_BASE . "_parent");
        \ilSession::clear(self::KEY_BASE . "_ref_ids");
    }
}
