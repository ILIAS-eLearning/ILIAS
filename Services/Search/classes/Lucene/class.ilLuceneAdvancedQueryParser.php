<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedQueryParser extends ilLuceneQueryParser
{
    protected $field_definition = null;
    protected $query_data = array();

    /**
     * Constructor
     */
    public function __construct($a_query_data)
    {
        parent::__construct('');
        
        $this->field_definition = ilLuceneAdvancedSearchFields::getInstance();
        $this->query_data = $a_query_data;
    }
    
    /**
     * Get field definition settings
     */
    public function getFieldDefinition()
    {
        return $this->field_definition;
    }
    
    public function getQueryData()
    {
        return $this->query_data ? $this->query_data : array();
    }
    
    public function parse()
    {
        foreach ((array) $this->getQueryData() as $field => $query) {
            if (!is_array($query) && !trim($query)) {
                continue;
            }
            $parsed = $this->getFieldDefinition()->parseFieldQuery($field, $query);
            if (strlen($parsed)) {
                $this->parsed_query .= " +(";
                $this->parsed_query .= $parsed;
                $this->parsed_query .= ") ";
            }
        }
        return true;
    }
}
