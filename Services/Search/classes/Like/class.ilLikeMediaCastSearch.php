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
* Class ilLikeMediaCastSearch
*
* Performs Mysql Like search
*
* @author Alex Killing <alex.killing@gmx.de>
*
*
*/

class ilLikeMediaCastSearch extends ilMediaCastSearch
{
    public function __createWhereCondition(): string
    {
        $and = "  WHERE context_obj_type='mcst' AND (  ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            $and .= $this->db->like('title', 'text', '%' . $word . '%');
            $and .= ' OR ';
            $and .= $this->db->like('content', 'clob', '%' . $word . '%');
        }
        return $and . ") ";
    }
}
