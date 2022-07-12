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
 
/**
 * Dummy access handler
 *
 * This can be used in contexts where no (proper) access handling is possible
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilDummyAccessHandler
{
    /**
     * check access for an object
     */
    public function checkAccess(string $permission, string $cmd, int $node_id, string $type = "") : bool
    {
        return true;
    }
}
