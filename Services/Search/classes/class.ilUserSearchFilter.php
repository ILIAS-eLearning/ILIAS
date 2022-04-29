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
* Generic user filter used for learning progress in courses, course member list ...
* Reads and stores user specific filter settings.
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @package ServicesSearch
*
*/
class ilUserSearchFilter
{
    private int $limit = 0;
    private bool $limit_reached = false;
    private bool $stored = false;


    private array $search_fields =
        [
            'login' => true,
            'firstname' => true,
            'lastname' => true
        ];

    private bool $enabled_member_filter = false;
    private array $possible_users = array();

    private int $usr_id;

    protected ilDBInterface $db;
    protected ilSearchResult $result_obj;
    protected ilSetting $settings;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * ilUserSearchFilter constructor.
     */
    public function __construct(int $a_usr_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->usr_id = $a_usr_id;
        $this->db = $DIC->database();
        $this->settings = $DIC->settings();

        // Limit of filtered objects is search max hits

        $this->limit = (int) $this->settings->get('search_mx_hits', '50');
        $this->result_obj = new ilSearchResult();
    }

    public function enableField(string $key) : void
    {
        $this->search_fields[$key] = true;
    }
    public function disableField(string $key) : void
    {
        $this->search_fields[$key] = true;
    }
    public function enableMemberFilter(bool $a_status) : void
    {
        $this->enabled_member_filter = $a_status;
    }

    public function setPossibleUsers(array $a_users) : void
    {
        $this->possible_users = $a_users ?: array();
    }


    public function getLimit() : int
    {
        return $this->limit;
    }

    public function limitReached() : bool
    {
        return $this->limit_reached;
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }
    
    public function storeQueryStrings(array $a_strings) : void
    {
        ilSession::set('search_usr_filter', $a_strings);
    }

    public function getQueryString(string $a_field) : string
    {
        $session_usr_filter = ilSession::get('search_usr_filter') ?? [];
        return $session_usr_filter[$a_field] ?? '';
    }


    public function getUsers() : array
    {
        // Check if a query string is given
        foreach ($this->search_fields as $field => $enabled) {
            if (!$enabled) {
                continue;
            }
            $session_search_usr_filter = (array) (ilSession::get('search_usr_filter') ?? []);
            $filter_field = (string) ($session_search_usr_filter[$field] ?? '');
            if (strlen($filter_field)) {
                $search = true;
                break;
            }
        }
        return $this->possible_users;
    }


    public function __searchObjects() : array
    {
        foreach ($this->search_fields as $field => $enabled) {
            // Disabled ?
            if (!$enabled) {
                continue;
            }

            $session_usr_filter = ilSession::get('search_usr_filter');
            $query_string = (string) ($session_usr_filter[$field] ?? '');
            if (!$query_string) {
                continue;
            }
            if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
                $this->main_tpl->setOnScreenMessage('info', $query_parser);
                return [];
            }
            $user_search = ilObjectSearchFactory::_getUserSearchInstance($query_parser);
            $user_search->setFields(array($field));
            
            // store entries
            $result_obj = $user_search->performSearch();
            $this->__storeEntries($result_obj);
        }

        // no filter entries
        if (is_object($this->result_obj)) {
            if ($this->enabled_member_filter) {
                $this->result_obj->addObserver($this, 'memberFilter');
            }
            $this->result_obj->filter(ROOT_FOLDER_ID, true);

            return $this->__toArray($this->result_obj->getResults());
        }
        return [];
    }

    /**
    * parse query string, using query parser instance
    * @return ilQueryParser | string of query parser or error message if an error occured
    * @access public
    */
    public function __parseQueryString(string $a_string)
    {
        $query_parser = new ilQueryParser(ilUtil::stripSlashes($a_string));
        $query_parser->setCombination(ilQueryParser::QP_COMBINATION_OR);
        $query_parser->setMinWordLength(1);
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }


    public function __storeEntries(ilSearchResult $new_res) : bool
    {
        if ($this->stored == false) {
            $this->result_obj->mergeEntries($new_res);
            $this->stored = true;
            return true;
        } else {
            $this->result_obj->intersectEntries($new_res);
            return true;
        }
    }

    public function __toArray(array $entries) : array
    {
        $users = [];
        foreach ($entries as $entry) {
            $users[] = $entry['obj_id'];
        }
        return $users ?: array();
    }


    public function memberFilter(int $a_usr_id, array $entry_data) : bool
    {
        return in_array($a_usr_id, $this->possible_users);
    }
}
