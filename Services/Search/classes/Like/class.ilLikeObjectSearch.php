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
* Class ilLikeObjectSearch
*
* Performs Mysql Like search in object_data title and description
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilObjectSearch.php';

class ilLikeObjectSearch extends ilObjectSearch
{
    public function __createWhereCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        /*
        $concat  = " CONCAT(";
        $concat .= 'title,description';
        $concat .= ") ";
        */
        
        $concat = $ilDB->concat(
            array(
                array('title','text'),
                array('description','text'))
        );
        

        $where = "WHERE (";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }
            
            $where .= $ilDB->like($concat, 'text', '%' . $word . '%');
            
            #$where .= $concat;
            #$where .= ("LIKE ('%".$word."%') ");
        }
        $where .= ') ';
        return $where;
    }
}
