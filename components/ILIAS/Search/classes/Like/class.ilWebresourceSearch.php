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
* Class ilWebresouceSearch
*
* Abstract class for glossary definitions.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilWebresourceSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        $this->setFields(array('title'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT webr_id, link_id " .
            $locate .
            "FROM webr_items " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->webr_id,
                'webr',
                $this->__prepareFound($row),
                (int) $row->link_id
            );
        }
        return $this->search_result;
    }
}
