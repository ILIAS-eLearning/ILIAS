<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomSmilies
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomSmilies
{
    /**
     * Inserts default smiley set
     */
    private static function _insertDefaultValues()
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $values = array(
            array("icon_smile.gif", ":)\n:-)\n:smile:"),
            array("icon_wink.gif", ";)\n;-)\n:wink:"),
            array("icon_laugh.gif", ":D\n:-D\n:laugh:\n:grin:\n:biggrin:"),
            array("icon_sad.gif", ":(\n:-(\n:sad:"),
            array("icon_shocked.gif", ":o\n:-o\n:shocked:"),
            array("icon_tongue.gif", ":p\n:-p\n:tongue:"),
            array("icon_cool.gif", ":cool:"),
            array("icon_eek.gif", ":eek:"),
            array("icon_angry.gif", ":||\n:-||\n:angry:"),
            array("icon_flush.gif", ":flush:"),
            array("icon_idea.gif", ":idea:"),
            array("icon_thumbup.gif", ":thumbup:"),
            array("icon_thumbdown.gif", ":thumbdown:"),
        );

        $stmt = $ilDB->prepareManip(
            "
			INSERT INTO chatroom_smilies (smiley_id, smiley_keywords, smiley_path)
			VALUES (?, ?, ?)",
            array("integer", "text", "text")
        );

        foreach ($values as $val) {
            $row = array(
                $ilDB->nextId("chatroom_smilies"),
                $val[1],
                $val[0]
            );
            $stmt->execute($row);
        }
    }

    /**
     * Checks if smiley folder is available; if not
     * it will try to create folder and performs
     * actions for an initial smiley set
     * @return boolean
     */
    public static function _checkSetup()
    {
        global $DIC;

        /** @var $lng ilLanguage */
        $lng = $DIC->language();

        $path = self::_getSmileyDir();

        if (!is_dir($path)) {
            ilUtil::sendInfo($lng->txt('chatroom_smilies_dir_not_exists'));
            ilUtil::makeDirParents($path);

            if (!is_dir($path)) {
                ilUtil::sendFailure($lng->txt('chatroom_smilies_dir_not_available'));
                return false;
            } else {
                $smilies = array(
                    "icon_smile.gif",
                    "icon_wink.gif",
                    "icon_laugh.gif",
                    "icon_sad.gif",
                    "icon_shocked.gif",
                    "icon_tongue.gif",
                    "icon_cool.gif",
                    "icon_eek.gif",
                    "icon_angry.gif",
                    "icon_flush.gif",
                    "icon_idea.gif",
                    "icon_thumbup.gif",
                    "icon_thumbdown.gif",
                );

                foreach ($smilies as $smiley) {
                    copy("templates/default/images/emoticons/$smiley", $path . "/$smiley");
                }

                self::_insertDefaultValues();
                ilUtil::sendSuccess($lng->txt('chatroom_smilies_initialized'));
            }
        }

        if (!is_writable($path)) {
            ilUtil::sendInfo($lng->txt('chatroom_smilies_dir_not_writable'));
        }

        return true;
    }

    /**
     * Path to smilies
     * @return string
     */
    public static function _getSmileyDir()
    {
        return ilUtil::getWebspaceDir() . '/chatroom/smilies';
    }

    /**
     * Fetches smilies from database.
     * @return array
     */
    public static function _getSmilies()
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $res    = $ilDB->query("SELECT smiley_id, smiley_keywords, smiley_path FROM chatroom_smilies");
        $result = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            $result[] = array(
                "smiley_id"       => $row['smiley_id'],
                "smiley_keywords" => $row['smiley_keywords'],
                "smiley_path"     => $row['smiley_path'],
                "smiley_fullpath" => ilUtil::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
            );
        }

        return $result;
    }

    /**
     * Deletes multiple smilies by given id array.
     * @global ilDBInterface $ilDB
     * @param array          $ids
     */
    public static function _deleteMultipleSmilies($ids = array())
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $smilies = self::_getSmiliesById($ids);

        if (count($smilies) <= 0) {
            return;
        }

        $sql_parts = array();

        foreach ($smilies as $s) {
            unlink($s["smiley_fullpath"]);
            $sql_parts[] = "smiley_id = " . $ilDB->quote($s["smiley_id"], 'integer');
        }

        $ilDB->manipulate("DELETE FROM chatroom_smilies WHERE " . implode(" OR ", $sql_parts));
    }

    /**
     * Fetches smilies from database by id.
     * @param array          $ids
     * @return array
     */
    public static function _getSmiliesById($ids = array())
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        if (!count($ids)) {
            return;
        }

        $sql = "SELECT smiley_id, smiley_keywords, smiley_path FROM chatroom_smilies WHERE ";

        $sql_parts = array();

        foreach ($ids as $id) {
            $sql_parts[] .= "smiley_id = " . $ilDB->quote($id, "integer");
        }

        $sql .= join(" OR ", $sql_parts);
        $res    = $ilDB->query($sql);
        $result = array();

        while ($row = $ilDB->fetchAssoc($res)) {
            $result[] = array(
                "smiley_id"       => $row['smiley_id'],
                "smiley_keywords" => $row['smiley_keywords'],
                "smiley_path"     => $row['smiley_path'],
                "smiley_fullpath" => ilUtil::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
            );
        }

        return $result;
    }

    /**
     * Updates smiley in DB by keyword and id from given array
     * ($data["smiley_keywords"], $data["smiley_id"])
     * @param array          $data
     */
    public static function _updateSmiley($data)
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            "UPDATE chatroom_smilies
					SET smiley_keywords = %s
				WHERE
					smiley_id = %s",
            array('text', 'integer'),
            array($data["smiley_keywords"], $data["smiley_id"])
        );

        if ($data["smiley_path"]) {
            $sm = self::_getSmiley($data["smiley_id"]);
            unlink($sm["smiley_fullpath"]);
            $ilDB->manipulateF(
                "UPDATE chatroom_smilies
						SET smiley_path = %s
					WHERE
						smiley_id = %s",
                array('text', 'integer'),
                array($data["smiley_path"], $data["smiley_id"])
            );
        }
    }

    /**
     * Looks up and returns smiley with id,
     * throws exception if id is not found
     * @global ilDBInterface $ilDB
     * @param integer        $a_id
     * @return string
     */
    public static function _getSmiley($a_id)
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "
			SELECT smiley_id, smiley_keywords, smiley_path
			FROM chatroom_smilies
			WHERE smiley_id = %s ",
            array('integer'),
            array($a_id)
        );

        if ($ilDB->numRows($res)) {
            while ($row = $ilDB->fetchAssoc($res)) {
                return array(
                    "smiley_id"       => $row['smiley_id'],
                    "smiley_keywords" => $row['smiley_keywords'],
                    "smiley_path"     => $row['smiley_path'],
                    "smiley_fullpath" => ilUtil::getWebspaceDir() . '/chatroom/smilies/' . $row['smiley_path']
                );
            }
        }

        throw new Exception('smiley with id $a_id not found');
    }

    /**
     * Returns smilies basepath.
     * @return string
     */
    public static function getSmiliesBasePath()
    {
        return 'chatroom/smilies';
    }

    /**
     * Deletes smiliey by given id from database.
     * @param integer        $a_id
     */
    public static function _deleteSmiley($a_id)
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        try {
            $smiley = self::_getSmiley($a_id);
            $path   = ilUtil::getWebspaceDir() . '/chatroom/smilies/' . $smiley["smiley_path"];

            if (is_file($path)) {
                unlink($path);
            }

            $ilDB->manipulateF(
                "DELETE FROM chatroom_smilies
				WHERE
					smiley_id = %s",
                array('integer'),
                array($a_id)
            );
        } catch (Exception $e) {
        }
    }

    /**
     * Stores smiley with given keywords and path in database.
     * @param array          $keywords
     * @param string         $path
     */
    public static function _storeSmiley($keywords, $path)
    {
        global $DIC;

        /** @var $ilDB ilDBInterface */
        $ilDB = $DIC->database();

        $stmt = $ilDB->prepareManip(
            "
			INSERT INTO chatroom_smilies (smiley_id, smiley_keywords, smiley_path)
			VALUES (?, ?, ?)",
            array(
                "integer", "text", "text"
            )
        );
        $row  = array(
            $ilDB->nextId("chatroom_smilies"),
            $keywords,
            $path
        );
        $stmt->execute($row);
    }

    /**
     * Trims given keywords and returns them in one array.
     * @param string $words
     * @return array
     */
    public static function _prepareKeywords($words)
    {
        $keywordscheck = true;

        // check keywords
        $keywords_unchecked = explode("\n", $words);
        if (count($keywords_unchecked) <= 0) {
            $keywordscheck = false;
        }

        if ($keywordscheck) {
            $keywords = array();

            foreach ($keywords_unchecked as $word) {
                if (trim($word)) {
                    $keywords[] = trim($word);
                }
            }
        }

        if ($keywordscheck && count($keywords) <= 0) {
            $keywordscheck = false;
        }

        if ($keywordscheck) {
            return $keywords;
        } else {
            return array();
        }
    }
}
