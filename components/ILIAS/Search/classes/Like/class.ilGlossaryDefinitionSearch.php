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
* Abstract class for glossary definitions.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilGlossaryDefinitionSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        // Search in glossary term

        $this->setFields(array('term'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT glo_id,id  " .
            $locate .
            "FROM glossary_term " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->glo_id,
                'glo',
                $this->__prepareFound($row),
                (int) $row->id
            );
        }

        return $this->search_result;
    }
}
