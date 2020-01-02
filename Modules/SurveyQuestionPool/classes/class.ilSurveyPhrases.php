<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey phrases class
*
* The ilSurveyPhrases class manages survey phrases (collections of survey categories)
* for ordinal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class ilSurveyPhrases
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $arrData;
    
    /**
    * ilSurveyPhrases constructor
    */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->arrData = array();
    }
    
    /**
    * Gets the available phrases from the database
    *
    * @param boolean $useronly Returns only the user defined phrases if set to true. The default is false.
    * @return array All available phrases as key/value pairs
    */
    public static function _getAvailablePhrases($useronly = 0)
    {
        global $DIC;

        $ilUser = $DIC->user();
        global $DIC;

        $ilDB = $DIC->database();
        global $DIC;

        $lng = $DIC->language();
        
        $phrases = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_phrase WHERE defaultvalue = %s OR owner_fi = %s ORDER BY title",
            array('text', 'integer'),
            array('1', $ilUser->getId())
        );
        while ($row = $ilDB->fetchObject($result)) {
            if (($row->defaultvalue == 1) and ($row->owner_fi == 0)) {
                if (!$useronly) {
                    $phrases[$row->phrase_id] = array(
                        "title" => $lng->txt($row->title),
                        "owner" => $row->owner_fi,
                        "org_title" => $row->title
                    );
                }
            } else {
                if ($ilUser->getId() == $row->owner_fi) {
                    $phrases[$row->phrase_id] = array(
                        "title" => $row->title,
                        "owner" => $row->owner_fi
                    );
                }
            }
        }
        return $phrases;
    }
    
    /**
    * Gets the available categories for a given phrase
    *
    * @param integer $phrase_id The database id of the given phrase
    * @return array All available categories
    */
    public static function _getCategoriesForPhrase($phrase_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        global $DIC;

        $lng = $DIC->language();
        
        $categories = array();
        $result = $ilDB->queryF(
            "SELECT svy_category.* FROM svy_category, svy_phrase_cat WHERE svy_phrase_cat.category_fi = svy_category.category_id AND svy_phrase_cat.phrase_fi = %s ORDER BY svy_phrase_cat.sequence",
            array('integer'),
            array($phrase_id)
        );
        while ($row = $ilDB->fetchObject($result)) {
            if (($row->defaultvalue == 1) and ($row->owner_fi == 0)) {
                $categories[$row->category_id] = $lng->txt($row->title);
            } else {
                $categories[$row->category_id] = $row->title;
            }
        }
        return $categories;
    }
    
    /**
    * Delete phrases from the database
    *
    * @param array $phrase_array An array containing phrase id's to delete
    */
    public function deletePhrases($phrase_array)
    {
        $ilDB = $this->db;
        
        if ((is_array($phrase_array)) && (count($phrase_array))) {
            $affectedRows = $ilDB->manipulate("DELETE FROM svy_phrase WHERE " . $ilDB->in('phrase_id', $phrase_array, false, 'integer'));
            $affectedRows = $ilDB->manipulate("DELETE FROM svy_phrase_cat WHERE " . $ilDB->in('phrase_fi', $phrase_array, false, 'integer'));
        }
    }
    
    /**
    * Saves a set of categories to a default phrase
    *
    * @param array $phrases The database ids of the seleted phrases
    * @param string $title The title of the default phrase
    * @access public
    */
    public function updatePhrase($phrase_id)
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $affectedRows = $ilDB->manipulateF(
            "UPDATE svy_phrase SET title = %s, tstamp = %s WHERE phrase_id = %s",
            array('text','integer','integer'),
            array($this->title, time(), $phrase_id)
        );

        $affectedRows = $ilDB->manipulateF(
            "DELETE FROM svy_phrase_cat WHERE phrase_fi = %s",
            array('integer'),
            array($phrase_id)
        );

        $counter = 1;
        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $cat = $this->categories->getCategory($i);
            $next_id = $ilDB->nextId('svy_category');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_category (category_id, title, defaultvalue, owner_fi, tstamp, neutral) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer','text','text','integer','integer','text'),
                array($next_id, $cat->title, 1, $ilUser->getId(), time(), $cat->neutral)
            );
            $category_id = $next_id;
            $next_id = $ilDB->nextId('svy_phrase_cat');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_phrase_cat (phrase_category_id, phrase_fi, category_fi, sequence) VALUES (%s, %s, %s, %s)",
                array('integer', 'integer', 'integer','integer'),
                array($next_id, $phrase_id, $category_id, $counter)
            );
            $counter++;
        }
    }
    
    /**
    * Saves a set of categories to a default phrase
    */
    public function savePhrase()
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $next_id = $ilDB->nextId('svy_phrase');
        $affectedRows = $ilDB->manipulateF(
            "INSERT INTO svy_phrase (phrase_id, title, defaultvalue, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s)",
            array('integer','text','text','integer','integer'),
            array($next_id, $this->title, 1, $ilUser->getId(), time())
        );
        $phrase_id = $next_id;

        $counter = 1;
        for ($i = 0; $i < $this->categories->getCategoryCount(); $i++) {
            $cat = $this->categories->getCategory($i);
            $next_id = $ilDB->nextId('svy_category');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_category (category_id, title, defaultvalue, owner_fi, tstamp, neutral) VALUES (%s, %s, %s, %s, %s, %s)",
                array('integer','text','text','integer','integer','text'),
                array($next_id, $cat->title, 1, $ilUser->getId(), time(), $cat->neutral)
            );
            $category_id = $next_id;
            $next_id = $ilDB->nextId('svy_phrase_cat');
            $affectedRows = $ilDB->manipulateF(
                "INSERT INTO svy_phrase_cat (phrase_category_id, phrase_fi, category_fi, sequence) VALUES (%s, %s, %s, %s)",
                array('integer', 'integer', 'integer','integer'),
                array($next_id, $phrase_id, $category_id, $counter)
            );
            $counter++;
        }
    }
    
    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            default:
                if (array_key_exists($value, $this->arrData)) {
                    return $this->arrData[$value];
                } else {
                    return null;
                }
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
}
