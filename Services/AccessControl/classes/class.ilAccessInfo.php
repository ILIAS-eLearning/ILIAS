<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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

    public function clear() : void
    {
        $this->info_items = [];
    }

    /**
     * add an info item
     */
    public function addInfoItem(string $a_type, string $a_text, string $a_data = "") : void
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
    public function getInfoItems() : array
    {
        return $this->info_items;
    }
}
