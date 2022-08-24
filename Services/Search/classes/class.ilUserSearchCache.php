<?php

declare(strict_types=1);
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
*
*
* @ilCtrl_Calls
* @ingroup ServicesSearch
*/
class ilUserSearchCache
{
    public const DEFAULT_SEARCH = 0;
    public const ADVANCED_SEARCH = 1;
    public const ADVANCED_MD_SEARCH = 4;
    public const LUCENE_DEFAULT = 5;
    public const LUCENE_ADVANCED = 6;

    public const LAST_QUERY = 7;

    public const LUCENE_USER_SEARCH = 8;

    private static ?ilUserSearchCache $instance = null;
    protected ilDBInterface $db;

    private int $usr_id;
    private int $search_type = self::DEFAULT_SEARCH;

    private array $search_result = array();
    private array $checked = array();
    private array $failed = array();
    private int $page_number = 1;

    /**
     * @var string|array $query
     */
    private $query;
    private int $root;
    private array $item_filter = array();
    private bool $isAnonymous = false;
    private array $mime_filter = array();
    private array $creation_filter = array();



    /**
     * Constructor
     *
     * @access private
     *
     */
    private function __construct(int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        if ($a_usr_id == ANONYMOUS_USER_ID) {
            $this->isAnonymous = true;
        }

        $this->root = ROOT_FOLDER_ID;
        $this->usr_id = $a_usr_id;
        $this->search_type = self::DEFAULT_SEARCH;
        $this->read();
    }

    public static function _getInstance(int $a_usr_id): ilUserSearchCache
    {
        if (self::$instance instanceof ilUserSearchCache) {
            return self::$instance;
        }
        return self::$instance = new ilUserSearchCache($a_usr_id);
    }

    /**
     * Check if current user is anonymous user
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    /**
     * switch to search type
     * reads entries from database
     */
    public function switchSearchType(int $a_type): bool
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
    public function getResults(): array
    {
        return $this->search_result ?: array();
    }

    /**
     * Set results
     *
     * @access public
     * @param array(int => array(int,int,string)) array(ref_id => array(ref_id,obj_id,type))
     *
     */
    public function setResults(array $a_results): void
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
    public function addResult(array $a_result_item): bool
    {
        $this->search_result[$a_result_item['ref_id']]['ref_id'] = $a_result_item['ref_id'];
        $this->search_result[$a_result_item['ref_id']]['obj_id'] = $a_result_item['obj_id'];
        $this->search_result[$a_result_item['ref_id']]['type'] = $a_result_item['type'];
        return true;
    }

    /**
     * Append failed id
     */
    public function appendToFailed(int $a_ref_id): void
    {
        $this->failed[$a_ref_id] = $a_ref_id;
    }

    /**
     * check if reference has failed access
     */
    public function isFailed(int $a_ref_id): bool
    {
        return in_array($a_ref_id, $this->failed);
    }

    /**
     * Append checked id
     *
     * @access public
     * @param int checked reference id
     * @param int checked obj_id
     *
     */
    public function appendToChecked(int $a_ref_id, int $a_obj_id): void
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
    public function isChecked(int $a_ref_id): bool
    {
        return array_key_exists($a_ref_id, $this->checked) and $this->checked[$a_ref_id];
    }

    /**
     * Get all checked items
     * @access public
     * @return array array(ref_id => obj_id)
     */
    public function getCheckedItems(): array
    {
        return $this->checked ?: array();
    }

    /**
     * Set result page number
     *
     * @access public
     *
     */
    public function setResultPageNumber(int $a_number): void
    {
        if ($a_number) {
            $this->page_number = $a_number;
        }
    }

    /**
     * get result page number
     */
    public function getResultPageNumber(): int
    {
        return $this->page_number ?: 1;
    }

    /**
     * set query
     * @param mixed query string or array (for advanced search)
     * @return void
     */
    public function setQuery($a_query): void
    {
        $this->query = $a_query;
    }

    /**
     * @return string|array query string or array (for advanced search)
     */
    public function getQuery()
    {
        if (is_array($this->query)) {
            return $this->query;
        }
        return $this->query ?? '';
    }

    /**
     * Urlencode query for further use in e.g glossariers (highlighting off search terms).
     */
    public function getUrlEncodedQuery(): string
    {
        if (is_array($this->getQuery())) {
            $query = $this->getQuery();

            return urlencode(str_replace('"', '.', $query['lom_content']));
        }
        return urlencode(str_replace('"', '.', $this->getQuery()));
    }

    /**
     * set root node of search
     */
    public function setRoot(int $a_root): void
    {
        $this->root = $a_root;
    }

    /**
     * get root node
     * @return int
     */
    public function getRoot(): int
    {
        return $this->root ?: ROOT_FOLDER_ID;
    }

    public function setItemFilter(array $a_filter): void
    {
        $this->item_filter = $a_filter;
    }

    public function getItemFilter(): array
    {
        return $this->item_filter;
    }

    public function setMimeFilter(array $a_filter): void
    {
        $this->mime_filter = $a_filter;
    }

    public function getMimeFilter(): array
    {
        return $this->mime_filter;
    }

    public function setCreationFilter(array $a_filter): void
    {
        $this->creation_filter = $a_filter;
    }

    public function getCreationFilter(): array
    {
        return $this->creation_filter;
    }


    /**
     * delete cached entries
     */
    public function deleteCachedEntries(): void
    {
        if ($this->isAnonymous()) {
            $this->deleteCachedEntriesAnonymous();
            return;
        }
        $query = "SELECT COUNT(*) num FROM usr_search " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " " .
            "AND search_type = " . $this->db->quote($this->search_type, 'integer');
        $res = $this->db->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        if ($row->num > 0) {
            $this->db->update(
                'usr_search',
                array(
                    'search_result' => array('clob',serialize(array(0))),
                    'checked' => array('clob',serialize(array(0))),
                    'failed' => array('clob',serialize(array(0))),
                    'page' => array('integer',0)),
                array(
                    'usr_id' => array('integer', $this->usr_id),
                    'search_type' => array('integer', $this->search_type)
            )
            );
        } else {
            $this->db->insert(
                'usr_search',
                array(
                    'search_result' => array('clob',serialize(array(0))),
                    'checked' => array('clob',serialize(array(0))),
                    'failed' => array('clob',serialize(array(0))),
                    'page' => array('integer',0),
                    'usr_id' => array('integer', $this->usr_id),
                    'search_type' => array('integer', $this->search_type)
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
    public function deleteCachedEntriesAnonymous(): bool
    {
        $this->setResultPageNumber(1);
        $this->search_result = array();
        $this->checked = array();
        $this->failed = array();

        return true;
    }



    public function delete(): bool
    {
        $query = "DELETE FROM usr_search " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " " .
            "AND search_type = " . $this->db->quote($this->search_type, 'integer');
        $res = $this->db->manipulate($query);

        $this->read();
        return true;
    }

    public function save(): void
    {
        if ($this->isAnonymous()) {
            $this->saveForAnonymous();
            return;
        }

        $query = "DELETE FROM usr_search " .
            "WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer') . " " .
            "AND ( search_type = " . $this->db->quote($this->search_type, 'integer') . ' ' .
            "OR search_type = " . $this->db->quote(self::LAST_QUERY, 'integer') . ')';
        $res = $this->db->manipulate($query);

        $this->db->insert('usr_search', array(
            'usr_id' => array('integer', $this->usr_id),
            'search_result' => array('clob',serialize($this->search_result)),
            'checked' => array('clob',serialize($this->checked)),
            'failed' => array('clob',serialize($this->failed)),
            'page' => array('integer', $this->page_number),
            'search_type' => array('integer', $this->search_type),
            'query' => array('clob',serialize($this->getQuery())),
            'root' => array('integer',$this->getRoot()),
            'item_filter' => array('text',serialize($this->getItemFilter())),
            'mime_filter' => array('text',  serialize($this->getMimeFilter())),
            'creation_filter' => array('text', serialize($this->getCreationFilter()))
        ));


        // Write last query information
        $this->db->insert(
            'usr_search',
            array(
                'usr_id' => array('integer',$this->usr_id),
                'search_type' => array('integer',self::LAST_QUERY),
                'query' => array('text',serialize($this->getQuery()))
            )
        );
    }

    public function saveForAnonymous(): void
    {
        ilSession::clear('usr_search_cache');
        $session_usr_search = [];
        $session_usr_search[$this->search_type]['search_result'] = $this->search_result;
        $session_usr_search[$this->search_type]['checked'] = $this->checked;
        $session_usr_search[$this->search_type]['failed'] = $this->failed;
        $session_usr_search[$this->search_type]['page'] = $this->page_number;
        $session_usr_search[$this->search_type]['query'] = $this->getQuery();
        $session_usr_search[$this->search_type]['root'] = $this->getRoot();
        $session_usr_search[$this->search_type]['item_filter'] = $this->getItemFilter();
        $session_usr_search[$this->search_type]['mime_filter'] = $this->getMimeFilter();
        $session_usr_search[$this->search_type]['creation_filter'] = $this->getCreationFilter();
        $session_usr_search[self::LAST_QUERY]['query'] = $this->getQuery();
        ilSession::set('usr_search_cache', $session_usr_search);
    }


    /**
     * Read user entries
     *
     * @access private
     *
     */
    private function read(): void
    {
        $this->failed = array();
        $this->checked = array();
        $this->search_result = array();
        $this->page_number = 0;

        if ($this->isAnonymous()) {
            $this->readAnonymous();
            return;
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
            $this->page_number = (int) $row->page;
            $this->setQuery(unserialize($row->query));
            $this->setRoot((int) $row->root);
            $this->setItemFilter(unserialize($row->item_filter));
            $this->setCreationFilter(unserialize($row->creation_filter));
        }
    }

    /**
     * Read from session for anonymous user
     */
    private function readAnonymous(): void
    {
        $usr_search_cache = ilSession::get('usr_search_cache') ?? [];

        $this->search_result = (array) ($usr_search_cache[$this->search_type]['search_result'] ?? []);
        $this->checked = (array) ($usr_search_cache[$this->search_type]['checked'] ?? []);
        $this->failed = (array) ($usr_search_cache[$this->search_type]['failed'] ?? []);
        $this->page_number = (int) ($usr_search_cache[$this->search_type]['page_number'] ?? 1);
        $this->setQuery((string) ($usr_search_cache[$this->search_type]['query'] ?? ''));
        $this->setRoot((int) ($usr_search_cache[$this->search_type]['root'] ?? ROOT_FOLDER_ID));
        $this->setItemFilter((array) ($usr_search_cache[$this->search_type]['item_filter'] ?? []));
        $this->setMimeFilter((array) ($usr_search_cache[$this->search_type]['mime_filter'] ?? []));
        $this->setCreationFilter((array) ($usr_search_cache[$this->search_type]['creation_filter'] ?? []));
    }
}
