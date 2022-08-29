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

/**
 * class ilAccessInfo
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAccessControl
 */
class ilAccessInfo
{
    public const IL_NO_PERMISSION = 'no_permission';
    public const IL_MISSING_PRECONDITION = "missing_precondition";
    public const IL_NO_OBJECT_ACCESS = "no_object_access";
    public const IL_NO_PARENT_ACCESS = "no_parent_access";
    public const IL_DELETED = 'object_deleted';
    public const IL_STATUS_INFO = 'object_status';
    public const IL_STATUS_MESSAGE = self::IL_STATUS_INFO;

    private array $info_items = [];

    public function clear(): void
    {
        $this->info_items = [];
    }

    /**
     * add an info item
     */
    public function addInfoItem(string $a_type, string $a_text, string $a_data = ""): void
    {
        $this->info_items[] = array(
            "type" => $a_type,
            "text" => $a_text,
            "data" => $a_data
        );
    }

    /**
     * get all info items
     */
    public function getInfoItems(): array
    {
        return $this->info_items;
    }
}
