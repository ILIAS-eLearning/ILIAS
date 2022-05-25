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

namespace ILIAS\MediaPool\Clipboard;

/**
 * Stores media pool clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ClipboardSessionRepository
{
    public const KEY_BASE = "mep_clip";

    public function __construct()
    {
    }

    public function setFolder(int $fold_id) : void
    {
        \ilSession::set(self::KEY_BASE . "_folder", $fold_id);
    }

    public function getFolder() : int
    {
        if (\ilSession::has(self::KEY_BASE . "_folder")) {
            return (int) \ilSession::get(self::KEY_BASE . "_folder");
        }
        return 0;
    }

    public function setIds(array $ids) : void
    {
        \ilSession::set(self::KEY_BASE . "_ids", $ids);
    }

    public function getIds() : array
    {
        if (\ilSession::has(self::KEY_BASE . "_ids")) {
            return \ilSession::get(self::KEY_BASE . "_ids");
        }
        return [];
    }
}
