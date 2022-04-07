<?php declare(strict_types=0);
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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseParticipant extends ilParticipant
{
    protected const COMPONENT_NAME = 'Modules/Course';

    protected static array $instances = [];

    /**
     * @todo get rid of these pseudo constants
     */
    protected function __construct(int $a_obj_id, int $a_usr_id)
    {
        $this->type = 'crs';

        parent::__construct(self::COMPONENT_NAME, $a_obj_id, $a_usr_id);
    }

    public static function _getInstanceByObjId(int $a_obj_id, int $a_usr_id) : ilCourseParticipant
    {
        if (isset(self::$instances[$a_obj_id][$a_usr_id]) && self::$instances[$a_obj_id][$a_usr_id]) {
            return self::$instances[$a_obj_id][$a_usr_id];
        }
        return self::$instances[$a_obj_id][$a_usr_id] = new ilCourseParticipant($a_obj_id, $a_usr_id);
    }
}
