<?php

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

declare(strict_types=1);
/**
* Class ilMediaCastSearch
*
* Abstract class for mediacast definitions.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilMediaCastSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        // Search in glossary term

        $this->setFields(array('title','content'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT id, context_obj_id, context_obj_type " .
            $locate .
            "FROM il_news_item " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->context_obj_id,
                'mcst',
                $this->__prepareFound($row),
                (int) $row->id
            );
        }
        return $this->search_result;
    }
}
