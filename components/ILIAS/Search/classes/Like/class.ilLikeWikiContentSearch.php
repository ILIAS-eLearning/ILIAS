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
* Class ilLikeWikiContentSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @package ilias-search
*
*/
class ilLikeWikiContentSearch extends ilWikiContentSearch
{
    public function __createWhereCondition(): string
    {
        $and = "  WHERE ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            $and .= $this->db->like("content", "clob", '%' . $word . '%');
            $and .= " OR ";
            $and .= $this->db->like("title", "text", '%' . $word . '%');
        }
        return $and . ") ";
    }
}
