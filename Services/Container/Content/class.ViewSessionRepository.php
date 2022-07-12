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

namespace ILIAS\Container\Content;

use ilSession;

/**
 * Stores view status
 * @author Alexander Killing <killing@leifos.de>
 */
class ViewSessionRepository
{
    protected const KEY = "cont_view";
    protected const VIEW_ADMIN = "admin";
    protected const VIEW_CONTENT = "content";

    public function __construct()
    {
    }

    public function setAdminView() : void
    {
        ilSession::set(self::KEY, self::VIEW_ADMIN);
    }

    public function setContentView() : void
    {
        ilSession::clear(self::KEY);
    }

    public function isAdminView() : bool
    {
        if (ilSession::has(self::KEY)) {
            return (ilSession::get(self::KEY) == self::VIEW_ADMIN);
        }
        return false;
    }

    public function isContentView() : bool
    {
        return !ilSession::has(self::KEY);
    }
}
