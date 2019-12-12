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
* Class ilForumSearch
*
* Performs Mysql Like search in object_data title and description
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilForumSearch.php';

class ilLikeForumSearch extends ilForumSearch
{
    public function __createPostAndCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        /*
        $concat  = " CONCAT(";
        $concat .= 'pos_message,pos_subject';
        $concat .= ") ";
        */
        $concat = $ilDB->concat(
            array(
                array('pos_subject','text'),
                array('pos_message','text'))
        );

        $and = "  AND ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            $and .= $ilDB->like($concat, 'clob', '%' . $word . '%');
        }
        return $and . ") ";
    }

    public function __createTopicAndCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $field = 'thr_subject ';
        $and = " AND( ";

        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            #$and .= $field;
            #$and .= ("LIKE ('%".$word."%')");
            $and .= $ilDB->like($field, 'text', '%' . $word . '%');
        }
        return $and . " ) ";
    }
}
