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

include_once 'Services/Search/classes/class.ilObjectSearchFactory.php';
include_once 'Services/Search/classes/class.ilSearchResult.php';

/**
* Generic user filter used for learning progress in courses, course member list ...
* Reads and stores user specific filter settings.
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ServicesSearch
*
*/
class ilUserSearchFilter
{
    public $limit = 0;
    public $limit_reached = false;


    public $search_fields = array('login' => true,
                               'firstname' => true,
                               'lastname' => true);

    public $enabled_member_filter = false;
    public $possible_users = array();

    // Default values for filter

    public $usr_id = null;
    public $db = null;

    public function __construct($a_usr_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilias = $DIC['ilias'];

        $this->usr_id = $a_usr_id;
        $this->db =&$ilDB;

        // Limit of filtered objects is search max hits
        $this->limit = $ilias->getSetting('search_max_hits', 50);
        $this->result_obj = new ilSearchResult();
    }

    public function enableField($key)
    {
        $this->search_fields[$key] = true;
    }
    public function disableField($key)
    {
        $this->search_fields[$key] = true;
    }
    public function enableMemberFilter($a_status)
    {
        $this->enabled_member_filter = $a_status;
    }

    public function setPossibleUsers($a_users)
    {
        $this->possible_users = $a_users ? $a_users : array();
    }


    public function getLimit()
    {
        return $this->limit;
    }

    public function limitReached()
    {
        return $this->limit_reached;
    }

    public function getUserId()
    {
        return $this->usr_id;
    }
    
    public function storeQueryStrings($a_strings)
    {
        $_SESSION['search_usr_filter'] = $a_strings;
    }

    public function getQueryString($a_field)
    {
        return isset($_SESSION['search_usr_filter'][$a_field]) ? $_SESSION['search_usr_filter'][$a_field] : '';
    }


    public function getUsers()
    {
        // Check if a query string is given
        foreach ($this->search_fields as $field => $enabled) {
            if (!$enabled) {
                continue;
            }
            if (strlen($_SESSION['search_usr_filter'][$field])) {
                $search = true;
                break;
            }
        }
        if ($search) {
            return $this->__searchObjects();
        } else {
            return $this->possible_users;
        }
    }


    public function __searchObjects()
    {
        foreach ($this->search_fields as $field => $enabled) {
            // Disabled ?
            if (!$enabled) {
                continue;
            }

            $query_string = $_SESSION['search_usr_filter'][$field];
            if (!$query_string) {
                continue;
            }
            if (!is_object($query_parser = $this->__parseQueryString($query_string))) {
                ilUtil::sendInfo($query_parser);
                return false;
            }
            $user_search =&ilObjectSearchFactory::_getUserSearchInstance($query_parser);
            $user_search->setFields(array($field));
            
            // store entries
            $this->__storeEntries($result_obj = $user_search->performSearch());
        }

        // no filter entries
        if (is_object($this->result_obj)) {
            if ($this->enabled_member_filter) {
                $this->result_obj->addObserver($this, 'memberFilter');
            }
            $this->result_obj->filter(ROOT_FOLDER_ID, QP_COMBINATION_OR);

            return $this->__toArray($this->result_obj->getResults());
        }
        return array();
    }

    /**
    * parse query string, using query parser instance
    * @return object of query parser or error message if an error occured
    * @access public
    */
    public function &__parseQueryString($a_string)
    {
        include_once 'Services/Search/classes/class.ilQueryParser.php';

        $query_parser = new ilQueryParser(ilUtil::stripSlashes($a_string));
        $query_parser->setCombination(QP_COMBINATION_OR);
        $query_parser->setMinWordLength(1, true);
        $query_parser->parse();

        if (!$query_parser->validate()) {
            return $query_parser->getMessage();
        }
        return $query_parser;
    }


    public function __storeEntries(&$new_res)
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

    public function __toArray($entries)
    {
        foreach ($entries as $entry) {
            $users[] = $entry['obj_id'];
        }
        return $users ? $users : array();
    }

    public function memberFilter($a_usr_id, $entry_data)
    {
        return in_array($a_usr_id, $this->possible_users);
    }
}
