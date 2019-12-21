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

include_once 'Services/Search/classes/class.ilAdvancedSearch.php';

/**
* Class ilFulltextMetaDataSearch
*
* class for searching meta
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id
*
* @package ilias-search
*
*/
class ilFulltextAdvancedSearch extends ilAdvancedSearch
{
    public function __createTaxonWhereCondition()
    {
        // IN BOOLEAN MODE
        $query .= " WHERE MATCH(taxon) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }
    public function __createKeywordWhereCondition()
    {
        // IN BOOLEAN MODE
        $query .= " WHERE MATCH(keyword) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }

    public function __createLifecycleWhereCondition()
    {
        // IN BOOLEAN MODE
        $query .= " WHERE MATCH(meta_version) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }

    public function __createEntityWhereCondition()
    {
        // IN BOOLEAN MODE
        $query .= " WHERE MATCH(entity) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }
    public function __createCoverageAndCondition()
    {
        // IN BOOLEAN MODE
        $query .= " AND MATCH(coverage) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }

    public function __createTitleDescriptionWhereCondition()
    {
        // IN BOOLEAN MODE
        $query .= " WHERE MATCH(title,description) AGAINST('";
        foreach ($this->query_parser->getQuotedWords(true) as $word) {
            $query .= $word;
            $query .= '* ';
        }
        $query .= "' IN BOOLEAN MODE) ";
        return $query;
    }
}
