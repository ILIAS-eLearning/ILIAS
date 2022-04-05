<?php declare(strict_types=1);
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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @package ilias-search
*
*/
class ilLikeAdvancedSearch extends ilAdvancedSearch
{
    public function __createTaxonWhereCondition() : string
    {
        if ($this->options['lom_taxon']) {
            $where = " WHERE (";
            
            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }
                
                $where .= $this->db->like('taxon', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }
    
    public function __createKeywordWhereCondition() : string
    {
        $where = " WHERE (";
        
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }
            
            $where .= $this->db->like('keyword', 'text', '%' . $word . '%');
        }
        $where .= ') ';
        return $where;
    }
    
    public function __createLifecycleWhereCondition() : string
    {
        if ($this->options['lom_version']) {
            $where = " WHERE (";
            
            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }
                
                $where .= $this->db->like('meta_version', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }
    
    public function __createEntityWhereCondition() : string
    {
        if ($this->options['lom_role_entry']) {
            $where = " WHERE (";
            
            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }
                
                $where .= $this->db->like('entity', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }

    public function __createCoverageAndCondition() : string
    {
        if ($this->options['lom_coverage']) {
            $where = " AND (";
            
            $counter = 0;
            foreach ($this->query_parser->getQuotedWords() as $word) {
                if ($counter++) {
                    $where .= "OR";
                }
                
                $where .= $this->db->like('coverage', 'text', '%' . $word . '%');
            }
            $where .= ') ';
            return $where;
        }
        return '';
    }
    
    public function __createTitleDescriptionWhereCondition() : string
    {
        $concat = $this->db->concat(
            array(
                array('title','text'),
                array('description','text'))
        );

        $where = " WHERE (";

        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }
            
            $where .= $this->db->like($concat, 'text', '%' . $word . '%');
        }
        $where .= ') ';
        
        return $where;
    }
}
