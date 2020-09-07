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

/**
* Class for storing search result. Allows paging of result sets
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesSearch
*/
class ilUserSearchCache
{
    const DEFAULT_SEARCH = 0;
    const ADVANCED_SEARCH = 1;
    const ADVANCED_MD_SEARCH = 4;
    const LUCENE_DEFAULT = 5;
    const LUCENE_ADVANCED = 6;
    
    const LAST_QUERY = 7;
    
    const LUCENE_USER_SEARCH = 8;

    private static $instance = null;
    private $db;
    
    private $usr_id;
    private $search_type = self::DEFAULT_SEARCH;
    
    private $search_result = array();
    private $checked = array();
    private $failed = array();
    private $page_number = 1;
    private $query;
    private $root = ROOT_FOLDER_ID;
    
    private $item_filter = array();

    private $isAnonymous = false;
    
    // begin-patch mime_filter
    private $mime_filter = array();
    // end-patch mime_filter
    
    // begin-patch create_date
    private $creation_filter = array();
    // end-patch create_date
    
    
    
    /**
     * Constructor
     *
     * @access private
     *
     */
    private function __construct($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_usr_id == ANONYMOUS_USER_ID) {
            $this->isAnonymous = true;
        }
        
        $this->db = $ilDB;
        $this->usr_id = $a_usr_id;
        $this->search_type = self::DEFAULT_SEARCH;
        $this->read();
    }
    
    /**
     * Get singleton instance
     *
     * @access public
     * @static
     *
     * @param int usr_id
     */
    public static function _getInstance($a_usr_id)
    {
        if (is_object(self::$instance) and self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilUserSearchCache($a_usr_id);
    }

    /**
     * Check if current user is anonymous user
     * @return bool
     */
    public function isAnonymous()
    {
        return $this->isAnonymous;
    }
    
    /**
     * switch to search type
     * reads entries from database
     *
     * @access public
     * @param int search type
     *
     */
    public function switchSearchType($a_type)
    {
        $this->search_type = $a_type;
        $this->read();
        return true;
    }
    
    /**
     * Get results
     *
     * @access public
     *
     */
    public function getResults()
    {
        return $this->search_result ? $this->search_result : array();
    }
    
    /**
     * Set results
     *
     * @access public
     * @param array(int => array(int,int,string)) array(ref_id => array(ref_id,obj_id,type))
     *
     */
    public function setResults($a_results)
    {
        $this->search_result = $a_results;
    }
    
    /**
     * Append result
     *
     * @access public
     * @param array(int,int,string) array(ref_id,obj_id,type)
     *
     */
    public function addResult($a_result_item)
    {
        $this->search_result[$a_result_item['ref_id']]['ref_id'] = $a_result_item['ref_id'];
        $this->search_result[$a_result_item['ref_id']]['obj_id'] = $a_result_item['obj_id'];
        $this->search_result[$a_result_item['ref_id']]['type'] = $a_result_item['type'];
        return true;
    }

    /**
     * Append failed id
     *
     * @access public
     * @param int ref_id of failed access
     *
     */
    public function appendToFailed($a_ref_id)
    {
        $this->failed[$a_ref_id] = $a_ref_id;
    }
    
    /**
     * check if reference has failed access
     *
     * @access public
     * @param int ref_id
     *
     */
    public function isFailed($a_ref_id)
    {
        return in_array($a_ref_id, $this->failed) ? true : false;
    }
    
    /**
     * Append checked id
     *
     * @access public
     * @param int checked reference id
     * @param int checked obj_id
     *
     */
    public function appendToChecked($a_ref_id, $a_obj_id)
    {
        $this->checked[$a_ref_id] = $a_obj_id;
    }
    
    /**
     * Check if reference was already checked
     *
     * @access public
     * @param int ref_id
     *
     */
    public function isChecked($a_ref_id)
    {
        return array_key_exists($a_ref_id, $this->checked) and $this->checked[$a_ref_id];
    }
    
    /**
     * Get all checked items
     *
     * @access public
     * @return array array(ref_id => obj_id)
     *
     */
    public function getCheckedItems()
    {
        return $this->checked ? $this->checked : array();
    }
    
    /**
     * Set result page number
     *
     * @access public
     *
     */
    public function setResultPageNumber($a_number)
    {
        if ($a_number) {
            $this->page_number = $a_number;
        }
    }
    
    /**
     * get result page number
     *
     * @access public
     *
     */
    public function getResultPageNumber()
    {
        return $this->page_number ? $this->page_number : 1;
    }
    
    /**
     * set query
     * @param mixed query string or array (for advanced search)
     * @return
     */
    public function setQuery($a_query)
    {
        $this->query = $a_query;
    }
    
    /**
     * get query
     *
     * @return
     */
    public function getQuery()
    {
        return $this->query;
    }
    
    /**
     * Urlencode query for further use in e.g glossariers (highlighting off search terms).
     * @return
     */
    public function getUrlEncodedQuery()
    {
        if (is_array($this->getQuery())) {
            $query = $this->getQuery();
            
            return urlencode(str_replace('"', '.', $query['lom_content']));
        }
        return urlencode(str_replace('"', '.', $this->getQuery()));
    }
    
    /**
     * set root node of search
     * @param int root id
     * @return
     */
    public function setRoot($a_root)
    {
        $this->root = $a_root;
    }
    
    /**
     * get root node
     *
     * @return
     */
    public function getRoot()
    {
        return $this->root ? $this->root : ROOT_FOLDER_ID;
    }
    
    public function setItemFilter($a_filter)
    {
        $this->item_filter = $a_filter;
    }
    
    public function getItemFilter()
    {
        return (array) $this->item_filter;
    }
    
    public function setMimeFilter($a_filter)
    {
        $this->mime_filter = $a_filter;
    }
    
    public function getMimeFilter()
    {
        return (array) $this->mime_filter;
    }
    
    // begin-patch create_date
    public function setCreationFilter($a_filter)
    {
        $this->creation_filter = $a_filter;
    }
    
    public function getCreationFilter()
    {
        return $this->creation_filter;
    }
    // end-patch create_date
    

    /**
     * delete cached entries
     * @param
     * @return
     */
    public function deleteCachedEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($this->isAnonymous()) {
            return $this->deleteCachedEntriesAnonymous();
        }

        
        $query = "SELECT COUNT(*) num FROM usr_search " .
            "WHERE usr_id = " . $ilDB->quote($this->usr_id, 'integer') . " " .
            "AND search_type = " . $ilDB->quote($this->search_type, 'integer');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        
        if ($row->num > 0) {
            $ilDB->update(
                'usr_search',
                array(
                    'search_result' => array('clob',serialize(array(0))),
                    'checked' => array('clob',serialize(array(0))),
                    'failed' => array('clob',serialize(array(0))),
                    'page' => array('integer',0)),
                array(
                    'usr_id' => array('integer',(int) $this->usr_id),
                    'search_type' => array('integer',(int) $this->search_type)
            )
            );
        } else {
            $ilDB->insert(
                'usr_search',
                array(
                    'search_result' => array('clob',serialize(array(0))),
                    'checked' => array('clob',serialize(array(0))),
                    'failed' => array('clob',serialize(array(0))),
                    'page' => array('integer',0),
                    'usr_id' => array('integer',(int) $this->usr_id),
                    'search_type' => array('integer',(int) $this->search_type)
            )
            );
        }
        
        $this->setResultPageNumber(1);
        $this->search_result = array();
        $this->checked = array();
        $this->failed = array();
    }

    /**
     * Delete cached entries for anonymous user
     * @return bool
     */
    public function deleteCachedEntriesAnonymous()
    {
        $this->setResultPageNumber(1);
        $this->search_result = array();
        $this->checked = array();
        $this->failed = array();

        return true;
    }


    
    /**
     * Delete user entries
     *
     * @access public
     *
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM usr_search " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " " .
            "AND search_type = " . $this->db->quote($this->search_type, 'integer');
        $res = $ilDB->manipulate($query);
        
        $this->read();
        return true;
    }
    
    /**
     * Save entries
     *
     * @access public
     *
     */
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->isAnonymous()) {
            return $this->saveForAnonymous();
        }
        
        $query = "DELETE FROM usr_search " .
            "WHERE usr_id = " . $ilDB->quote($this->usr_id, 'integer') . " " .
            "AND ( search_type = " . $ilDB->quote($this->search_type, 'integer') . ' ' .
            "OR search_type = " . $ilDB->quote(self::LAST_QUERY, 'integer') . ')';
        $res = $ilDB->manipulate($query);
        
        $ilDB->insert('usr_search', array(
            'usr_id' => array('integer',(int) $this->usr_id),
            'search_result' => array('clob',serialize($this->search_result)),
            'checked' => array('clob',serialize($this->checked)),
            'failed' => array('clob',serialize($this->failed)),
            'page' => array('integer',(int) $this->page_number),
            'search_type' => array('integer',(int) $this->search_type),
            'query' => array('clob',serialize($this->getQuery())),
            'root' => array('integer',$this->getRoot()),
            'item_filter' => array('text',serialize($this->getItemFilter())),
            'mime_filter' => array('text',  serialize($this->getMimeFilter())),
            'creation_filter' => array('text', serialize($this->getCreationFilter()))
        ));
            
            
        // Write last query information
        $ilDB->insert(
            'usr_search',
            array(
                'usr_id' => array('integer',$this->usr_id),
                'search_type' => array('integer',self::LAST_QUERY),
                'query' => array('text',serialize($this->getQuery()))
            )
        );
    }

    public function saveForAnonymous()
    {
        unset($_SESSION['usr_search_cache']);

        $_SESSION['usr_search_cache'][$this->search_type]['search_result'] = $this->search_result;
        $_SESSION['usr_search_cache'][$this->search_type]['checked'] = $this->checked;
        $_SESSION['usr_search_cache'][$this->search_type]['failed'] = $this->failed;
        $_SESSION['usr_search_cache'][$this->search_type]['page'] = $this->page_number;
        $_SESSION['usr_search_cache'][$this->search_type]['query'] = $this->getQuery();
        $_SESSION['usr_search_cache'][$this->search_type]['root'] = $this->getRoot();
        $_SESSION['usr_search_cache'][$this->search_type]['item_filter'] = $this->getItemFilter();
        $_SESSION['usr_search_cache'][$this->search_type]['mime_filter'] = $this->getMimeFilter();
        $_SESSION['usr_search_cache'][$this->search_type]['creation_filter'] = $this->getCreationFilter();

        $_SESSION['usr_search_cache'][self::LAST_QUERY]['query'] = $this->getQuery();

        return true;
    }
    
    
    /**
     * Read user entries
     *
     * @access private
     *
     */
    private function read()
    {
        $this->failed = array();
        $this->checked = array();
        $this->search_result = array();
        $this->page_number = 0;

        if ($this->isAnonymous()) {
            return $this->readAnonymous();
        }

        $query = "SELECT * FROM usr_search " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " " .
            "AND search_type = " . $this->db->quote($this->search_type, 'integer');
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result = unserialize(stripslashes($row->search_result));
            if (strlen($row->checked)) {
                $this->checked = unserialize(stripslashes($row->checked));
            }
            if (strlen($row->failed)) {
                $this->failed = unserialize(stripslashes($row->failed));
            }
            $this->page_number = $row->page;
            $this->setQuery(unserialize($row->query));
            $this->setRoot($row->root);
            $this->setItemFilter(unserialize($row->item_filter));
            $this->setCreationFilter(unserialize($row->creation_filter));
        }
        return true;
    }

    /**
     * Read from session for anonymous user
     */
    private function readAnonymous()
    {
        $this->search_result = (array) $_SESSION['usr_search_cache'][$this->search_type]['search_result'];
        $this->checked = (array) $_SESSION['usr_search_cache'][$this->search_type]['checked'];
        $this->failed = (array) $_SESSION['usr_search_cache'][$this->search_type]['failed'];
        $this->page_number = $_SESSION['usr_search_cache'][$this->search_type]['page_number'];

        $this->setQuery($_SESSION['usr_search_cache'][$this->search_type]['query']);
        $this->setRoot((string) $_SESSION['usr_search_cache'][$this->search_type]['root']);
        $this->setItemFilter((array) $_SESSION['usr_search_cache'][$this->search_type]['item_filter']);
        $this->setMimeFilter((array) $_SESSION['usr_search_cache'][$this->search_type]['mime_filter']);
        $this->setCreationFilter((array) $_SESSION['usr_search_cache'][$this->search_type]['creation_filter']);

        return true;
    }
}
