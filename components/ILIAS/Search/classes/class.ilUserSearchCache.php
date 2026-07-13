<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Class for storing search result. Allows paging of result sets
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilUserSearchCache
{
    public const int DEFAULT_SEARCH = 0;
    public const int LUCENE_DEFAULT = 5;

    public const int LAST_QUERY = 7;

    public const int LUCENE_USER_SEARCH = 8;

    private static ?ilUserSearchCache $instance = null;
    protected ilDBInterface $db;

    private int $usr_id;
    private int $search_type = self::DEFAULT_SEARCH;

    private array $search_result = [];
    private array $checked = [];
    private array $failed = [];
    private int $page_number = 1;

    /**
     * @var string|array $query
     */
    private $query;
    private int $root;
    private array $item_filter = [];
    private bool $isAnonymous = false;
    private array $mime_filter = [];
    private array $creation_filter = [];
    private array $copyright_filter = [];



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
        return $this->search_result ?: [];
    }

    /**
     * Set results
     *
     * @access public
     * @param array $a_results (int => array(int,int,string)) array(ref_id => array(ref_id,obj_id,type))
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
     * @param array $a_result_item (int,int,string) array(ref_id,obj_id,type)
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

    public function appendToChecked(int $a_ref_id, int $a_obj_id): void
    {
        $this->checked[$a_ref_id] = $a_obj_id;
    }

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
        return $this->checked ?: [];
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

    public function setQuery(string $a_query): void
    {
        $this->query = $a_query;
    }

    public function getQuery(): string
    {
        return $this->query ?? '';
    }

    /**
     * Urlencode query for further use in e.g glossariers (highlighting off search terms).
     */
    public function getUrlEncodedQuery(): string
    {
        return urlencode(str_replace('"', '.', $this->getQuery()));
    }

    /**
     * set root node of search
     */
    public function setRoot(int $a_root): void
    {
        $this->root = $a_root;
    }

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

    public function setCopyrightFilter(string ...$copyright_identifiers): void
    {
        $this->copyright_filter = $copyright_identifiers;
    }

    /**
     * @return string[] copyright identifiers
     */
    public function getCopyrightFilter(): array
    {
        return $this->copyright_filter;
    }

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
                [
                    'search_result' => ['clob', serialize([0])],
                    'checked' => ['clob', serialize([0])],
                    'failed' => ['clob', serialize([0])],
                    'page' => ['integer', 0]
                ],
                [
                    'usr_id' => ['integer', $this->usr_id],
                    'search_type' => ['integer', $this->search_type]
                ]
            );
        } else {
            $this->db->insert(
                'usr_search',
                [
                    'search_result' => ['clob', serialize([0])],
                    'checked' => ['clob', serialize([0])],
                    'failed' => ['clob', serialize([0])],
                    'page' => ['integer', 0],
                    'usr_id' => ['integer', $this->usr_id],
                    'search_type' => ['integer', $this->search_type],
                    'query' => ['clob', serialize('')]
                ]
            );
        }

        $this->setResultPageNumber(1);
        $this->search_result = [];
        $this->checked = [];
        $this->failed = [];
    }

    public function deleteCachedEntriesAnonymous(): bool
    {
        $this->setResultPageNumber(1);
        $this->search_result = [];
        $this->checked = [];
        $this->failed = [];

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

        $this->db->insert('usr_search', [
            'usr_id' => ['integer', $this->usr_id],
            'search_result' => ['clob', serialize($this->search_result)],
            'checked' => ['clob', serialize($this->checked)],
            'failed' => ['clob', serialize($this->failed)],
            'page' => ['integer', $this->page_number],
            'search_type' => ['integer', $this->search_type],
            'query' => ['clob', serialize($this->getQuery())],
            'root' => ['integer', $this->getRoot()],
            'item_filter' => ['text', serialize($this->getItemFilter())],
            'mime_filter' => ['text', serialize($this->getMimeFilter())],
            'creation_filter' => ['text', serialize($this->getCreationFilter())],
            'copyright_filter' => ['text', serialize($this->getCopyrightFilter())]
        ]);


        // Write last query information
        $this->db->insert(
            'usr_search',
            [
                'usr_id' => ['integer', $this->usr_id],
                'search_type' => ['integer', self::LAST_QUERY],
                'query' => ['text', serialize($this->getQuery())]
            ]
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
        $session_usr_search[$this->search_type]['copyright_filter'] = $this->getCopyrightFilter();
        $session_usr_search[self::LAST_QUERY]['query'] = $this->getQuery();
        ilSession::set('usr_search_cache', $session_usr_search);
    }

    private function read(): void
    {
        $this->failed = [];
        $this->checked = [];
        $this->search_result = [];
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
            $this->search_result = (array) unserialize((string) $row->search_result);
            if (strlen((string) $row->checked)) {
                $this->checked = (array) unserialize((string) $row->checked);
            }
            if (strlen((string) $row->failed)) {
                $this->failed = (array) unserialize((string) $row->failed);
            }
            $this->page_number = (int) $row->page;
            $this->setQuery((string) unserialize((string) $row->query));
            $this->setRoot((int) $row->root);
            $this->setItemFilter((array) unserialize((string) $row->item_filter));
            $this->setCreationFilter((array) unserialize((string) $row->creation_filter));
            if ($row->copyright_filter !== null) {
                $this->setCopyrightFilter(...(array) unserialize((string) $row->copyright_filter));
            }
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
        $this->setCopyrightFilter(...(array) ($usr_search_cache[$this->search_type]['copyright_filter'] ?? []));
    }
}
