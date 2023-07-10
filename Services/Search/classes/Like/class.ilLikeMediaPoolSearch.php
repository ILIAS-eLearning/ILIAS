<?php

declare(strict_types=1);
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
* Class ilLikeMediaPoolSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/

class ilLikeMediaPoolSearch extends ilMediaPoolSearch
{
    public function __createAndCondition(): string
    {
        $concat = $this->db->concat(
            array(
                array('title','text')
                )
        );


        $and = "  AND ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            #$and .= $ilDB->like($concat,'text','%'.$word.'%');
            $and .= $this->db->like('title', 'text', '%' . $word . '%');
        }
        return $and . ") ";
    }

    /**
     * Condition for mob keyword search
     * @return string
     */
    public function __createKeywordAndCondition(): string
    {
        $concat = ' keyword ';

        $and = "  WHERE  ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            $and .= $this->db->like($concat, 'text', '%' . $word . '%');
        }
        return $and;
    }
}
