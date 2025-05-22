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
* Class ilLMContentSearch
*
* Abstract class for lm content.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLMContentSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        $this->setFields(array('content'));

        $in = $this->__createInStatement();
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT page_id,parent_id,parent_type " .
            $locate .
            "FROM page_object, lm_data " .
            $where .
            "AND obj_id = page_id " .
            $in;


        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->parent_id,
                (string) $row->parent_type,
                $this->__prepareFound($row),
                (int) $row->page_id
            );
        }

        return $this->search_result;
    }



    // Protected can be overwritten in Like or Fulltext classes
    public function __createInStatement(): string
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->getFilter() and !$this->getIdFilter()) {
            return '';
        }

        $in = '';
        if ($this->getFilter()) {
            $type = "('";
            $type .= implode("','", $this->getFilter());
            $type .= "')";

            $in = " AND parent_type IN " . $type . ' ';
        }
        if ($this->getIdFilter()) {
            $in .= ' AND ';
            $in .= $ilDB->in('parent_id', $this->getIdFilter(), false, 'integer');
        }
        return $in;
    }
}
