<?php
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
* Class ilTestSearch
*
* Abstract class for test search. Should be inherited by ilFulltextTestSearch
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilTestSearch extends ilAbstractSearch
{
    public function &__searchTestIntroduction()
    {
        $this->setFields(array('introduction'));

        $where = $this->__createWhereCondition(implode(',', $this->getFields()));
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM tst_tests " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->obj_fi, 'tst', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
    public function &__searchTestTitle()
    {
        $this->setFields(array('title','description'));

        $where = $this->__createWhereCondition(implode(',', $this->getFields()));
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM qpl_questions " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->obj_fi, 'qpl', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
    public function &__searchSurveyIntroduction()
    {
        $this->setFields(array('introduction'));

        $where = $this->__createWhereCondition(implode(',', $this->getFields()));
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM svy_svy " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->obj_fi, 'svy', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
    public function &__searchSurveyTitle()
    {
        $this->setFields(array('title','description'));

        $where = $this->__createWhereCondition(implode(',', $this->getFields()));
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM svy_question " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->obj_fi, 'spl', $this->__prepareFound($row));
        }
        return $this->search_result;
    }


    public function performSearch()
    {
        $this->__searchTestTitle();
        $this->__searchTestIntroduction();
        $this->__searchSurveyTitle();
        $this->__searchSurveyIntroduction();

        return $this->search_result;
    }
}
