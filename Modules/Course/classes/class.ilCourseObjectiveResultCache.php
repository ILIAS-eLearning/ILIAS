<?php
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

include_once('./Modules/Course/classes/class.ilCourseObjectiveResult.php');

/**
* Caches results for a specific user and course
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
*/
class ilCourseObjectiveResultCache
{
    private static $suggested = null;
    private static $status = null;
    
    
    /**
     * check if objective is suggested
     *
     * @access public
     * @param int usr_id
     * @param int course_id
     * @return bool
     * @static
     */
    public static function isSuggested($a_usr_id, $a_crs_id, $a_objective_id)
    {
        if (!is_array(self::$suggested[$a_usr_id][$a_crs_id])) {
            self::$suggested[$a_usr_id][$a_crs_id] = self::readSuggested($a_usr_id, $a_crs_id);
        }
        return in_array($a_objective_id, self::$suggested[$a_usr_id][$a_crs_id]) ? true : false;
    }
    
    /**
     * get status of user
     *
     * @access public
     * @param int usr_id
     * @param int crs_id
     * @return
     * @static
     */
    public static function getStatus($a_usr_id, $a_crs_id)
    {
        if (isset(self::$status[$a_usr_id][$a_crs_id])) {
            return self::$status[$a_usr_id][$a_crs_id];
        }
        $tmp_res = new ilCourseObjectiveResult($a_usr_id);
        return self::$status[$a_usr_id][$a_crs_id] = $tmp_res->getStatus($a_crs_id);
    }
    
    
    /**
     * read suggested objectives
     *
     * @access protected
     * @param
     * @return
     */
    protected function readSuggested($a_usr_id, $a_crs_id)
    {
        return ilCourseObjectiveResult::_getSuggested($a_usr_id, $a_crs_id, self::getStatus($a_usr_id, $a_crs_id));
    }
}
