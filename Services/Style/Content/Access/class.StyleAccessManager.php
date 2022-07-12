<?php declare(strict_types=1);

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

namespace ILIAS\Style\Content\Access;

use ilRbacSystem;

/**
 * Manages access to content style editing
 * @author Alexander Killing <killing@leifos.de>
 */
class StyleAccessManager
{
    protected bool $enable_write = false;
    protected int $ref_id = 0;
    protected int $user_id = 0;
    protected ilRbacSystem $rbacsystem;

    public function __construct(ilRbacSystem $rbacsystem = null, int $ref_id = 0, int $user_id = 0)
    {
        global $DIC;

        $this->rbacsystem = (!is_null($rbacsystem))
            ? $rbacsystem
            : $DIC->rbac()->system();
        $this->ref_id = $ref_id;
        $this->user_id = $user_id;
    }

    public function enableWrite(bool $write) : void
    {
        $this->enable_write = $write;
    }

    public function checkWrite() : bool
    {
        $rbacsystem = $this->rbacsystem;
        if ($this->ref_id == 0) {
            return true;
        }

        return ($this->enable_write ||
            $rbacsystem->checkAccessOfUser(
                $this->user_id,
                "write",
                $this->ref_id
            ) ||
            $rbacsystem->checkAccessOfUser(
                $this->user_id,
                "sty_write_content",
                $this->ref_id
            )
        );
    }
}
