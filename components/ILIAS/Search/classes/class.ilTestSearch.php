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
* Class ilTestSearch
*
* Abstract class for test search.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilTestSearch extends ilAbstractSearch
{
    public function __searchTestIntroduction(): ilSearchResult
    {
        $this->setFields(array('introduction'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM tst_tests " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->obj_fi,
                'tst',
                $this->__prepareFound($row)
            );
        }
        return $this->search_result;
    }

    public function __searchTestTitle(): ilSearchResult
    {
        $this->setFields(array('title','description'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM qpl_questions " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->obj_fi,
                'qpl',
                $this->__prepareFound($row)
            );
        }
        return $this->search_result;
    }
    public function __searchSurveyIntroduction(): ilSearchResult
    {
        $this->setFields(array('introduction'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM svy_svy " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->obj_fi,
                'svy',
                $this->__prepareFound($row)
            );
        }
        return $this->search_result;
    }
    public function __searchSurveyTitle(): ilSearchResult
    {
        $this->setFields(array('title','description'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT obj_fi  " .
            $locate .
            "FROM svy_question " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->obj_fi,
                'spl',
                $this->__prepareFound($row)
            );
        }
        return $this->search_result;
    }


    public function performSearch(): ilSearchResult
    {
        $this->__searchTestTitle();
        $this->__searchTestIntroduction();
        $this->__searchSurveyTitle();
        $this->__searchSurveyIntroduction();
        return $this->search_result;
    }
}
