<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Data class for like feature. DB related operations.
 *
 * The like table only holds a record if an expression has been added. After a "dislike" the record disappears.
 * This reduces space and increases performance. But we do not know "when" something has been disliked.
 *
 * Since the subobject_type column is pk it must be not null and does not allow "" due to the abstract DB handling.
 * We internally save "" as "-" here.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesLike
 */
class ilLikeData
{
    const TYPE_LIKE = 0;
    const TYPE_DISLIKE = 1;
    const TYPE_LOVE = 2;
    const TYPE_LAUGH = 3;
    const TYPE_ASTOUNDED = 4;
    const TYPE_SAD = 5;
    const TYPE_ANGRY = 6;

    /**
     *
     * @var array
     */
    protected $data = array();

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     * @param ilDB $db
     */
    public function __construct(array $a_obj_ids = array(), ilDB $db = null, $lng = null)
    {
        global $DIC;

        $this->db = ($db == null)
            ? $DIC->database()
            : $db;

        $this->lng = ($lng == null)
            ? $DIC->language()
            : $lng;
        $this->loadDataForObjects($a_obj_ids);
        $this->lng->loadLanguageModule("like");
    }
    
    /**
     * Get types
     *
     * @param
     * @return array
     */
    public function getExpressionTypes()
    {
        return array(
            self::TYPE_LIKE => $this->lng->txt("like_like"),
            self::TYPE_DISLIKE => $this->lng->txt("like_dislike"),
            self::TYPE_LOVE => $this->lng->txt("like_love"),
            self::TYPE_LAUGH => $this->lng->txt("like_laugh"),
            self::TYPE_ASTOUNDED => $this->lng->txt("like_astounded"),
            self::TYPE_SAD => $this->lng->txt("like_sad"),
            self::TYPE_ANGRY => $this->lng->txt("like_angry")
        );
    }
    
    
    /**
     * Add expression for a user and object
     *
     * @param int $a_user_id user id (who is liking)
     * @param int $a_like_type one of self::TYPE_LIKE to self::TYPE_ANGRY
     * @param int $a_obj_id object id (must be an repository object id)
     * @param string $a_obj_type object type (redundant, for performance reasons)
     * @param int $a_sub_obj_id subobject id (as defined by the module being responsible for main object type)
     * @param string $a_sub_obj_type subobject type (as defined by the module being responsible for main object type)
     * @param int $a_news_id news is (optional news id, if like action is dedicated to a news for the object/subobject)
     */
    public function addExpression(
        $a_user_id,
        $a_like_type,
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id = 0,
        $a_sub_obj_type = "",
        $a_news_id = 0
    ) {
        $ilDB = $this->db;

        if ($a_user_id == ANONYMOUS_USER_ID) {
            return;
        }

        $this->data[$a_obj_id][$a_sub_obj_id][$a_sub_obj_type][$a_news_id][$a_like_type][$a_user_id] = 1;

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->replace(
            "like_data",
            array(
                "user_id" => array("integer", (int) $a_user_id),
                "obj_id" => array("integer", (int) $a_obj_id),
                "obj_type" => array("text", $a_obj_type),
                "sub_obj_id" => array("integer", (int) $a_sub_obj_id),
                "sub_obj_type" => array("text", $a_sub_obj_type),
                "news_id" => array("integer", (int) $a_news_id),
                "like_type" => array("integer", (int) $a_like_type)
                ),
            array(
                "exp_ts" => array("timestamp", ilUtil::now())
            )
        );
    }

    /**
     * Remove expression for a user and object
     *
     * @param int $a_user_id user id (who is liking)
     * @param int $a_like_type one of self::TYPE_LIKE to self::TYPE_ANGRY
     * @param int $a_obj_id object id (must be an repository object id)
     * @param string $a_obj_type object type (redundant, for performance reasons)
     * @param int $a_sub_obj_id subobject id (as defined by the module being responsible for main object type)
     * @param string $a_sub_obj_type subobject type (as defined by the module being responsible for main object type)
     * @param int $a_news_id news is (optional news id, if like action is dedicated to a news for the object/subobject)
     */
    public function removeExpression(
        $a_user_id,
        $a_like_type,
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id = 0,
        $a_sub_obj_type = "",
        $a_news_id = 0
    ) {
        $ilDB = $this->db;

        if ($a_user_id == ANONYMOUS_USER_ID) {
            return;
        }

        if (isset($this->data[$a_obj_id][$a_sub_obj_id][$a_sub_obj_type][$a_news_id][$a_like_type][$a_user_id])) {
            unset($this->data[$a_obj_id][$a_sub_obj_id][$a_sub_obj_type][$a_news_id][$a_like_type][$a_user_id]);
        }

        if ($a_sub_obj_type == "") {
            $a_sub_obj_type = "-";
        }

        $ilDB->manipulate(
            "DELETE FROM like_data WHERE " .
            " user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND obj_type = " . $ilDB->quote($a_obj_type, "text") .
            " AND sub_obj_id = " . $ilDB->quote($a_sub_obj_id, "integer") .
            " AND sub_obj_type = " . $ilDB->quote($a_sub_obj_type, "text") .
            " AND news_id = " . $ilDB->quote($a_news_id, "integer") .
            " AND like_type = " . $ilDB->quote($a_like_type, "integer")
        );
    }

    /**
     * Load data (for objects)
     *
     * @param int[] load data for objects
     */
    protected function loadDataForObjects($a_obj_ids = array())
    {
        $ilDB = $this->db;

        foreach ($a_obj_ids as $id) {
            $this->data[$id] = array();
        }

        $set = $ilDB->query("SELECT * FROM like_data " .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, false, "integer") .
            " ORDER by exp_ts DESC");
        while ($rec = $ilDB->fetchAssoc($set)) {
            $subtype = $rec["sub_obj_type"] == "-"
                ? ""
                : $rec["sub_obj_type"];
            $this->data[$rec["obj_id"]][$rec["sub_obj_id"]][$subtype][$rec["news_id"]][$rec["like_type"]][$rec["user_id"]] =
                $rec["exp_ts"];
        }
    }

    /**
     * Get expression counts for obj/subobj/news
     *
     * @param int $obj_id
     * @param string $obj_type
     * @param int $sub_obj_id
     * @param string $sub_obj_type
     * @param int $news_id
     * @return int[] $news_id
     * @throws ilLikeDataException
     */
    public function getExpressionCounts($obj_id, $obj_type, $sub_obj_id, $sub_obj_type, $news_id)
    {
        if (!is_array($this->data[$obj_id])) {
            include_once("./Services/Like/exceptions/class.ilLikeDataException.php");
            throw new ilLikeDataException("No data loaded for object $obj_id.");
        }

        if ($sub_obj_type == "-") {
            $sub_obj_type = "";
        }

        $cnt = array();
        foreach ($this->getExpressionTypes() as $k => $txt) {
            $cnt[$k] = 0;
            if (is_array($this->data[$obj_id][$sub_obj_id][$sub_obj_type][$news_id][$k])) {
                $cnt[$k] = count($this->data[$obj_id][$sub_obj_id][$sub_obj_type][$news_id][$k]);
            }
        }
        return $cnt;
    }

    /**
     * Is expression set for a user and object?
     *
     * @param int $a_user_id user id (who is liking)
     * @param int $a_like_type one of self::TYPE_LIKE to self::TYPE_ANGRY
     * @param int $a_obj_id object id (must be an repository object id)
     * @param string $a_obj_type object type (redundant, for performance reasons)
     * @param int $a_sub_obj_id subobject id (as defined by the module being responsible for main object type)
     * @param string $a_sub_obj_type subobject type (as defined by the module being responsible for main object type)
     * @param int $a_news_id news is (optional news id, if like action is dedicated to a news for the object/subobject)
     * @return bool
     */
    public function isExpressionSet(
        $a_user_id,
        $a_like_type,
        $a_obj_id,
        $a_obj_type,
        $a_sub_obj_id = 0,
        $a_sub_obj_type = "",
        $a_news_id = 0
    ) {
        if (isset($this->data[$a_obj_id][$a_sub_obj_id][$a_sub_obj_type][$a_news_id][$a_like_type][$a_user_id])) {
            return true;
        }
        return false;
    }

    /**
     * Get expression entries for obj/subobj/news
     *
     * @param int $obj_id
     * @param string $obj_type
     * @param int $sub_obj_id
     * @param string $sub_obj_type
     * @param int $news_id
     * @return int[] $news_id
     * @throws ilLikeDataException
     */
    public function getExpressionEntries($obj_id, $obj_type, $sub_obj_id, $sub_obj_type, $news_id)
    {
        if (!is_array($this->data[$obj_id])) {
            include_once("./Services/Like/exceptions/class.ilLikeDataException.php");
            throw new ilLikeDataException("No data loaded for object $obj_id.");
        }

        if ($sub_obj_type == "-") {
            $sub_obj_type = "";
        }

        $exp = array();
        foreach ($this->getExpressionTypes() as $k => $txt) {
            if (is_array($this->data[$obj_id][$sub_obj_id][$sub_obj_type][$news_id][$k])) {
                foreach ($this->data[$obj_id][$sub_obj_id][$sub_obj_type][$news_id][$k] as $user => $ts) {
                    $exp[] = array(
                        "expression" => $k,
                        "user_id" => $user,
                        "timestamp" => $ts
                    );
                }
            }
        }

        $exp = ilUtil::sortArray($exp, "timestamp", "desc");
        return $exp;
    }

    /**
     * Get expression entries for obj/subobj/news
     *
     * @param int $obj_id
     * @param int $since_ts timestamp (show only data since...)
     * @return array
     * @throws ilLikeDataException
     */
    public function getExpressionEntriesForObject($obj_id, $since_ts = null)
    {
        if (!is_array($this->data[$obj_id])) {
            include_once("./Services/Like/exceptions/class.ilLikeDataException.php");
            throw new ilLikeDataException("No data loaded for object $obj_id.");
        }
        $exp = array();
        foreach ($this->data[$obj_id] as $sub_obj_id => $si) {
            foreach ($si as $sub_obj_type => $so) {
                foreach ($so as $news_id => $ni) {
                    foreach ($ni as $exp_type => $entry) {
                        foreach ($entry as $user => $ts) {
                            if ($since_ts == null || $ts > $since_ts) {
                                $exp[] = array(
                                    "sub_obj_id" => $sub_obj_id,
                                    "sub_obj_type" => $sub_obj_type,
                                    "news_id" => $news_id,
                                    "expression" => $exp_type,
                                    "user_id" => $user,
                                    "timestamp" => $ts
                                );
                            }
                        }
                    }
                }
            }
        }

        $exp = ilUtil::sortArray($exp, "timestamp", "desc");
        return $exp;
    }
}
