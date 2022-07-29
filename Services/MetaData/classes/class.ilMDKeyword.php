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
 * Meta Data class (element keyword)
 * @package ilias-core
 * @version $Id$
 */
class ilMDKeyword extends ilMDBase
{
    private string $keyword = '';
    private ?ilMDLanguageItem $keyword_language = null;

    // SET/GET
    public function setKeyword(string $a_keyword) : void
    {
        $this->keyword = $a_keyword;
    }

    public function getKeyword() : string
    {
        return $this->keyword;
    }

    public function setKeywordLanguage(ilMDLanguageItem $lng_obj) : void
    {
        $this->keyword_language = $lng_obj;
    }

    public function getKeywordLanguage() : ?ilMDLanguageItem
    {
        return is_object($this->keyword_language) ? $this->keyword_language : null;
    }

    public function getKeywordLanguageCode() : string
    {
        return is_object($this->keyword_language) ? $this->keyword_language->getLanguageCode() : '';
    }

    public function save() : int
    {
        $fields = $this->__getFields();
        $fields['meta_keyword_id'] = array('integer', $next_id = $this->db->nextId('il_meta_keyword'));

        if ($this->db->insert('il_meta_keyword', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_keyword',
            $this->__getFields(),
            array("meta_keyword_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete() : bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_keyword " .
                "WHERE meta_keyword_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields() : array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'parent_type' => array('text', $this->getParentType()),
            'parent_id' => array('integer', $this->getParentId()),
            'keyword' => array('text', $this->getKeyword()),
            'keyword_language' => array('text', $this->getKeywordLanguageCode())
        );
    }

    public function read() : bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_keyword " .
                "WHERE meta_keyword_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType((string) $row->obj_type);
                $this->setParentId((int) $row->parent_id);
                $this->setParentType((string) $row->parent_type);
                $this->setKeyword((string) $row->keyword);
                $this->setKeywordLanguage(new ilMDLanguageItem($row->keyword_language));
            }
        }
        return true;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlElement(
            'Keyword',
            array(
                'Language' => $this->getKeywordLanguageCode() ?: 'en'
            ),
            $this->getKeyword()
        );
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(int $a_rbac_id, int $a_obj_id, int $a_parent_id, string $a_parent_type) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_keyword_id FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text') . " " .
            "ORDER BY meta_keyword_id ";

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_keyword_id;
        }
        return $ids;
    }

    /**
     * @return array<string, string[]>
     */
    public static function _getKeywordsByLanguage(int $a_rbac_id, int $a_obj_id, string $a_type) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilObjDataCache = $DIC['ilObjDataCache'];

        $query = "SELECT keyword,keyword_language " .
            "FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND obj_type = " . $ilDB->quote($a_type, 'text') . " ";
        $res = $ilDB->query($query);
        $keywords = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->keyword) {
                $keywords[$row->keyword_language][] = $row->keyword;
            }
        }
        return $keywords;
    }

    /**
     * @return array<string, string>
     */
    public static function _getKeywordsByLanguageAsString(int $a_rbac_id, int $a_obj_id, string $a_type) : array
    {
        $key_string = [];
        foreach (self::_getKeywordsByLanguage($a_rbac_id, $a_obj_id, $a_type) as $lng_code => $keywords) {
            $key_string[$lng_code] = implode(",", $keywords);
        }
        return $key_string;
    }

    /**
     * @return int[]
     */
    public static function _searchKeywords(string $a_query, string $a_type, int $a_rbac_id = 0) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

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
        $obj_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }

        return $obj_ids;
    }

    /**
     * @return string[]
     */
    public static function _getMatchingKeywords(string $a_query, string $a_type, int $a_rbac_id = 0) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT DISTINCT keyword FROM il_meta_keyword " .
            'WHERE obj_type = ' . $ilDB->quote($a_type, 'text') . ' ' .
            'AND ' . $ilDB->like('keyword', 'text', '%' . trim($a_query) . '%') . ' ';

        if ($a_rbac_id) {
            $query .= "AND rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . ' ';
        }

        $res = $ilDB->query($query);
        $kws = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $kws[] = $row->keyword;
        }
        return $kws;
    }

    public static function lookupKeywords(int $a_rbac_id, int $a_obj_id, bool $a_return_ids = false) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM il_meta_keyword " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . ' ' .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . ' ';
        $res = $ilDB->query($query);
        $kws = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$a_return_ids) {
                if (is_string($row->keyword) && $row->keyword !== '') {
                    $kws[] = $row->keyword;
                }
            } else {
                $kws[] = $row->meta_keyword_id;
            }
        }
        return $kws;
    }

    public static function updateKeywords(ilMDGeneral $a_md_section, array $a_keywords) : void
    {
        // trim keywords
        $new_keywords = array();
        foreach ($a_keywords as $lang => $keywords) {
            foreach ((array) $keywords as $keyword) {
                $keyword = trim($keyword);
                if ($keyword !== "" && !(isset($new_keywords[$lang]) && in_array($keyword, $new_keywords[$lang], true))) {
                    $new_keywords[$lang][] = $keyword;
                }
            }
        }

        // update existing author entries (delete if not entered)
        foreach ($ids = $a_md_section->getKeywordIds() as $id) {
            $md_key = $a_md_section->getKeyword($id);
            $lang = $md_key->getKeywordLanguageCode();

            // entered keyword already exists
            if (is_array($new_keywords[$lang] ?? false) && in_array($md_key->getKeyword(), $new_keywords[$lang], true)) {
                unset($new_keywords[$lang][array_search($md_key->getKeyword(), $new_keywords[$lang], true)]);
            } else {  // existing keyword has not been entered again -> delete
                $md_key->delete();
            }
        }

        // insert entered, but not existing keywords
        foreach ($new_keywords as $lang => $key_arr) {
            foreach ($key_arr as $keyword) {
                if ($keyword !== "") {
                    $md_key = $a_md_section->addKeyword();
                    $md_key->setKeyword(ilUtil::stripSlashes($keyword));
                    $md_key->setKeywordLanguage(new ilMDLanguageItem($lang));
                    $md_key->save();
                }
            }
        }
    }
}
