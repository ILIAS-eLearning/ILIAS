<?php

declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchCommandQueue
{
    private static ?self $instance = null;

    protected ilDBInterface $db;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * get singleton instance
     */
    public static function factory(): ilSearchCommandQueue
    {
        if (self::$instance instanceof ilSearchCommandQueue) {
            return self::$instance;
        }
        return self::$instance = new ilSearchCommandQueue();
    }

    /**
     * update / save new entry
     */
    public function store(ilSearchCommandQueueElement $element): void
    {
        $this->db->replace(
            'search_command_queue',
            [
                'obj_id' => [ilDBConstants::T_INTEGER, $element->getObjId()],
                'obj_type' => [ilDBConstants::T_TEXT, $element->getObjType()],
                'sub_id' => [ilDBConstants::T_INTEGER, 0]
            ],
            [
                'sub_type' => [ilDBConstants::T_TEXT, ''],
                'command' => [ilDBConstants::T_TEXT, $element->getCommand()],
                'last_update' => [ilDBConstants::T_DATE, $this->db->now()],
                'finished' => [ilDBConstants::T_INTEGER, 0]
            ]
        );
    }
}
