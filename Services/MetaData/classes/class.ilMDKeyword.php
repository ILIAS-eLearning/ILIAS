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
* Meta Data class (element keyword)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDKeyword extends ilMDBase
{
    // SET/GET
    public function setKeyword($a_keyword)
    {
        $this->keyword = $a_keyword;
    }
    public function getKeyword()
    {
        return $this->keyword;
    }
    public function setKeywordLanguage(&$lng_obj)
    {
        if (is_object($lng_obj)) {
            $this->keyword_language = $lng_obj;
        }
    }
    public function &getKeywordLanguage()
    {
        return is_object($this->keyword_language) ? $this->keyword_language : false;
    }
    public function getKeywordLanguageCode()
    {
        return is_object($this->keyword_language) ? $this->keyword_language->getLanguageCode() : false;
    }

    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->__getFields();
        $fields['meta_keyword_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_keyword'));
        
        if ($this->db->insert('il_meta_keyword', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_keyword',
                $this->__getFields(),
                array("meta_keyword_id" => array('integer',$this->getMetaId()))
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_keyword " .
                "WHERE meta_keyword_id = " . $ilDB->quote($this->getMetaId(), 'integer');
            $res = $ilDB->manipulate($query);
            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id' => array('integer',$this->getRBACId()),
                     'obj_id' => array('integer', $this->getObjId()),
                     'obj_type' => array('text', $this->getObjType()),
                     'parent_type' => array('text', $this->getParentType()),
                     'parent_id' => array('integer', $this->getParentId()),
                     'keyword' => array('text', $this->getKeyword()),
                     'keyword_language' => array('text', $this->getKeywordLanguageCode()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_keyword " .
                "WHERE meta_keyword_id = " . $ilDB->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setParentId($row->parent_id);
                $this->setParentType($row->parent_type);
                $this->setKeyword($row->keyword);
                $this->setKeywordLanguage(new ilMDLanguageItem($row->keyword_language));
            }
        }
        return true;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlElement(
            'Keyword',
            array('Language' => $this->getKeywordLanguageCode() ?
                                            $this->getKeywordLanguageCode() :
                                            'en'),
            $this->getKeyword()
        );
    }


    // STATIC
    public static function _getIds($a_rbac_id, $a_obj_id, $a_parent_id, $a_parent_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_keyword_id FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text') . " " .
            "ORDER BY meta_keyword_id ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = $row->meta_keyword_id;
        }
        return $ids ? $ids : array();
    }
    
    /**
     * Get keywords by language
     *
     * @access public
     * @static
     *
     * @param int rbac_id
     * @param int obj_id
     * @param string obj type
     */
    public static function _getKeywordsByLanguage($a_rbac_id, $a_obj_id, $a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        $query = "SELECT keyword,keyword_language " .
            "FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_type, 'text') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->keyword) {
                $keywords[$row->keyword_language][] = $row->keyword;
            }
        }
        return $keywords ? $keywords : array();
    }
    /**
     * Get keywords by language as string
     *
     * @access public
     * @static
     *
     * @param int rbac_id
     * @param int obj_id
     * @param string obj type
     */
    public static function _getKeywordsByLanguageAsString($a_rbac_id, $a_obj_id, $a_type)
    {
        foreach (ilMDKeyword::_getKeywordsByLanguage($a_rbac_id, $a_obj_id, $a_type) as $lng_code => $keywords) {
            $key_string[$lng_code] = implode(",", $keywords);
        }
        return $key_string ? $key_string : array();
    }
    
    /**
     * Search for objects by keywords
     * @param string $a_query
     * @param string $a_type
     * @param int $a_rbac_id [optional]
     * @return
     */
    public static function _searchKeywords($a_query, $a_type, $a_rbac_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        $qs = 'AND ';
        $counter = 0;
        foreach ((array) explode(' ', $a_query) as $part) {
            if ($counter++) {
                $qs .= 'OR ';
            }
            $qs .= ($ilDB->like('keyword', 'text', $part) . ' ');
        }

        if ($a_rbac_id) {
            $query = "SELECT obj_id FROM il_meta_keyword " .
                "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . ' ' .
                'AND obj_type = ' . $ilDB->quote($a_type, 'text') . ' ' .
                $qs;
        } else {
            $query = "SELECT obj_id FROM il_meta_keyword " .
                'WHERE obj_type = ' . $ilDB->quote($a_type, 'text') . ' ' .
                $qs;
        }

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        
        return (array) $obj_ids;
    }
    
    /**
     * Search for keywords
     *
     * @param string $a_query
     * @param string $a_type
     * @param int $a_rbac_id [optional]
     * @return
     */
    public static function _getMatchingKeywords($a_query, $a_type, $a_rbac_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT DISTINCT keyword FROM il_meta_keyword " .
                'WHERE obj_type = ' . $ilDB->quote($a_type, 'text') . ' ' .
                'AND ' . $ilDB->like('keyword', 'text', '%' . trim($a_query) . '%') . ' ';
        
        if ($a_rbac_id) {
            $query .= "AND rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . ' ';
        }
                        
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $kws[] = $row->keyword;
        }
        return (array) $kws;
    }
    
    /**
     * Lookup Keywords
     * @param object $a_rbac_id
     * @param object $a_obj_id
     * @param bool $a_return_kw
     * @return
     */
    public static function lookupKeywords($a_rbac_id, $a_obj_id, $a_return_ids = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . ' ' .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$a_return_ids) {
                if (strlen($row->keyword)) {
                    $kws[] = $row->keyword;
                }
            } else {
                $kws[] = $row->meta_keyword_id;
            }
        }
        return (array) $kws;
    }
    
    /**
     * Update keywords from input array
     * @param ilMDGeneral $a_md_section
     * @param array $a_keywords lang => keywords
     */
    public static function updateKeywords(ilMDGeneral $a_md_section, array $a_keywords)
    {
        // trim keywords
        $new_keywords = array();
        foreach ($a_keywords as $lang => $keywords) {
            foreach ((array) $keywords as $keyword) {
                $keyword = trim($keyword);
                if ($keyword != "" &&
                    !(is_array($new_keywords[$lang]) && in_array($keyword, $new_keywords[$lang]))) {
                    $new_keywords[$lang][] = $keyword;
                }
            }
        }
        
        // update existing author entries (delete if not entered)
        foreach ($ids = $a_md_section->getKeywordIds() as $id) {
            $md_key = $a_md_section->getKeyword($id);
            $lang = $md_key->getKeywordLanguageCode();

            // entered keyword already exists
            if (is_array($new_keywords[$lang]) &&
                in_array($md_key->getKeyword(), $new_keywords[$lang])) {
                unset($new_keywords[$lang]
                    [array_search($md_key->getKeyword(), $new_keywords[$lang])]);
            } else {  // existing keyword has not been entered again -> delete
                $md_key->delete();
            }
        }

        // insert entered, but not existing keywords
        foreach ($new_keywords as $lang => $key_arr) {
            foreach ($key_arr as $keyword) {
                if ($keyword != "") {
                    $md_key = $a_md_section->addKeyword();
                    $md_key->setKeyword(ilUtil::stripSlashes($keyword));
                    $md_key->setKeywordLanguage(new ilMDLanguageItem($lang));
                    $md_key->save();
                }
            }
        }
    }
}
