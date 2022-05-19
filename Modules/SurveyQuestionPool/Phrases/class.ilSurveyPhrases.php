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

/**
 * Survey phrases class
 *
 * The ilSurveyPhrases class manages survey phrases (collections of survey categories)
 * for ordinal survey question types.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @todo split up in dto and repo class
 */
class ilSurveyPhrases
{
    protected ilDBInterface $db;
    protected ilObjUser $user;

    protected array $arrData;
    
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->arrData = array();
    }
    
    /**
     * Gets the available phrases from the database
     * @param bool $useronly Returns only the user
     * defined phrases if set to true. The default is false.
     * @return array All available phrases as key/value pairs
     */
    public static function _getAvailablePhrases(
        bool $useronly = false
    ) : array {
        global $DIC;

        $ilUser = $DIC->user();
        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $phrases = array();
        $result = $ilDB->queryF(
            "SELECT * FROM svy_phrase WHERE defaultvalue = %s OR owner_fi = %s ORDER BY title",
            array('text', 'integer'),
            array('1', $ilUser->getId())
        );
        while ($row = $ilDB->fetchObject($result)) {
            if ((int) $row->defaultvalue === 1 && (int) $row->owner_fi === 0) {
                if (!$useronly) {
                    $phrases[$row->phrase_id] = array(
                        "title" => $lng->txt($row->title),
                        "owner" => $row->owner_fi,
                        "org_title" => $row->title
                    );
                }
            } elseif ($ilUser->getId() === (int) $row->owner_fi) {
                $phrases[$row->phrase_id] = array(
                    "title" => $row->title,
                    "owner" => $row->owner_fi
                );
            }
        }
        return $phrases;
    }
    
    /**
     * Gets the available categories for a given phrase
     * @param int $phrase_id The database id of the given phrase
     * @return array All available categories
     */
    public static function _getCategoriesForPhrase(
        int $phrase_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        
        $categories = array();
        $result = $ilDB->queryF(
            "SELECT svy_category.* FROM svy_category, svy_phrase_cat WHERE svy_phrase_cat.category_fi = svy_category.category_id AND svy_phrase_cat.phrase_fi = %s ORDER BY svy_phrase_cat.sequence",
            array('integer'),
            array($phrase_id)
        );
        while ($row = $ilDB->fetchObject($result)) {
            if ((int) $row->defaultvalue === 1 && (int) $row->owner_fi === 0) {
                $categories[$row->category_id] = $lng->txt($row->title);
            } else {
                $categories[$row->category_id] = $row->title;
            }
        }
        return $categories;
    }
    
    /**
     * Delete phrases from the database
     * @param array $phrase_array An array containing phrase id's to delete
     */
    public function deletePhrases(
        array $phrase_array
    ) : void {
        $ilDB = $this->db;
        
        if (count($phrase_array) > 0) {
            $ilDB->manipulate("DELETE FROM svy_phrase WHERE " . $ilDB->in('phrase_id', $phrase_array, false, 'integer'));
            $ilDB->manipulate("DELETE FROM svy_phrase_cat WHERE " . $ilDB->in('phrase_fi', $phrase_array, false, 'integer'));
        }
    }
    
    public function updatePhrase(
        int $phrase_id
    ) : void {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $ilDB->manipulateF(
            "UPDATE svy_phrase SET title = %s, tstamp = %s WHERE phrase_id = %s",
            array('text','integer','integer'),
            array($this->title, time(), $phrase_id)
        );

        $ilDB->manipulateF(
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
    public function savePhrase() : void
    {
        $ilUser = $this->user;
        $ilDB = $this->db;

        $next_id = $ilDB->nextId('svy_phrase');
        $ilDB->manipulateF(
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
     * @return mixed|null
     */
    public function __get(string $value)
    {
        switch ($value) {
            default:
                return $this->arrData[$value] ?? null;
        }
    }

    /**
     * @param mixed|null $value
     */
    public function __set(string $key, $value) : void
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
}
