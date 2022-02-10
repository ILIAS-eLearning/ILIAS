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
* Caches results for a specific user and course
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ingroup ModulesCourse
*/
class ilCourseObjectiveResultCache
{
    private static array $suggested = [];
    private static array $status = [];
    
    
    public static function isSuggested(int $a_usr_id, int $a_crs_id, int $a_objective_id) : bool
    {
        if (!is_array(self::$suggested[$a_usr_id][$a_crs_id])) {
            self::$suggested[$a_usr_id][$a_crs_id] = self::readSuggested($a_usr_id, $a_crs_id);
        }
        return in_array($a_objective_id, self::$suggested[$a_usr_id][$a_crs_id]);
    }
    
    public static function getStatus(int $a_usr_id, int $a_crs_id) : string
    {
        if (isset(self::$status[$a_usr_id][$a_crs_id])) {
            return self::$status[$a_usr_id][$a_crs_id];
        }
        $tmp_res = new ilCourseObjectiveResult($a_usr_id);
        return self::$status[$a_usr_id][$a_crs_id] = $tmp_res->getStatus($a_crs_id);
    }
    
    protected function readSuggested(int $a_usr_id, int $a_crs_id) : array
    {
        return ilCourseObjectiveResult::_getSuggested($a_usr_id, $a_crs_id, self::getStatus($a_usr_id, $a_crs_id));
    }
}
