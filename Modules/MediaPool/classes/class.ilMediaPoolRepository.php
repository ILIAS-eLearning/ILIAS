<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaPool;

/**
 * Media pool repository
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolRepository
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct($db = null)
    {
        global $DIC;

        $this->db = ($db)
            ? $db
            : $DIC->database();
    }

    /**
     * Get media objects
     * @param string $a_title_filter
     * @param string $a_format_filter
     * @param string $a_keyword_filter
     * @param        $a_caption_filter
     * @return array
     */
    protected function getMediaObjects(
        $pool_id,
        $title_filter = "",
        $format_filter = "",
        $keyword_filter = '',
        $caption_filter = ""
    ) {
        $db = $this->db;

        $query = "SELECT DISTINCT mep_tree.*, object_data.* " .
            "FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) " .
            " JOIN object_data ON (mep_item.foreign_id = object_data.obj_id) ";

        if ($format_filter != "" or $caption_filter != '') {
            $query .= " JOIN media_item ON (media_item.mob_id = object_data.obj_id) ";
        }

        $query .=
            " WHERE mep_tree.mep_id = " . $db->quote($pool_id, "integer") .
            " AND object_data.type = " . $db->quote("mob", "text");

        // filter
        if (trim($title_filter) != "") {    // title
            $query .= " AND " . $db->like("object_data.title", "text", "%" . trim($title_filter) . "%");
        }
        if (!in_array($format_filter, ["", "mob"])) {            // format
            $filter = ($format_filter == "unknown")
                ? ""
                : $format_filter;
            $query .= " AND " . $db->equals("media_item.format", $filter, "text", true);
        }
        if (trim($caption_filter)) {
            $query .= 'AND ' . $db->like('media_item.caption', 'text', '%' . trim($caption_filter) . '%');
        }

        $query .=
            " ORDER BY object_data.title";

        $objs = array();
        $set = $db->query($query);
        while ($rec = $db->fetchAssoc($set)) {
            $rec["foreign_id"] = $rec["obj_id"];
            $rec["obj_id"] = "";
            $objs[] = $rec;
        }

        // Keyword filter
        if ($keyword_filter) {
            include_once './Services/MetaData/classes/class.ilMDKeyword.php';
            $res = \ilMDKeyword::_searchKeywords($keyword_filter, 'mob', 0);

            foreach ($objs as $obj) {
                if (in_array($obj['foreign_id'], $res)) {
                    $filtered[] = $obj;
                }
            }
            return (array) $filtered;
        }
        return $objs;
    }

    /**
     * Get media objects
     * @param string $a_title_filter
     * @param string $a_format_filter
     * @param string $a_keyword_filter
     * @param        $a_caption_filter
     * @return array
     */
    protected function getContentSnippets(
        $pool_id,
        $title_filter = "",
        $format_filter = "",
        $keyword_filter = '',
        $caption_filter = ""
    ) {
        // format filter snippets come with internal "pg" format
        if (!in_array($format_filter, ["pg", ""])) {
            return [];
        }

        // snippets do not have no caption
        if ($caption_filter != "") {
            return [];
        }

        $db = $this->db;

        $query = "SELECT DISTINCT mep_tree.*, mep_item.* " .
            "FROM mep_tree JOIN mep_item ON (mep_tree.child = mep_item.obj_id) ";

        $query .=
            " WHERE mep_tree.mep_id = " . $db->quote($pool_id, "integer") .
            " AND mep_item.type = " . $db->quote("pg", "text");

        // filter
        if (trim($title_filter) != "") {    // title
            $query .= " AND " . $db->like("mep_item.title", "text", "%" . trim($title_filter) . "%");
        }

//        $query .=
//            " ORDER BY object_data.title";

        $objs = array();
        $set = $db->query($query);
        while ($rec = $db->fetchAssoc($set)) {
            //$rec["foreign_id"] = $rec["obj_id"];
            //$rec["obj_id"] = "";
            $objs[] = $rec;
        }

        // Keyword filter
        if ($keyword_filter) {
            include_once './Services/MetaData/classes/class.ilMDKeyword.php';
            $res = \ilMDKeyword::_searchKeywords($keyword_filter, 'mpg', $pool_id);
            foreach ($objs as $obj) {
                if (in_array($obj['obj_id'], $res)) {
                    $filtered[] = $obj;
                }
            }
            return (array) $filtered;
        }
        return $objs;
    }

    /**
     * @param int $pool_id
     * @param string $title_filter
     * @param string $format_filter
     * @param string $keyword_filter
     * @param string $caption_filter
     * @return array
     */
    public function getItems(
        $pool_id,
        $title_filter = "",
        $format_filter = "",
        $keyword_filter = "",
        $caption_filter = ""
    ) {
        $mobs = $this->getMediaObjects($pool_id,
            $title_filter,
            $format_filter,
            $keyword_filter,
            $caption_filter);

        $snippets = $this->getContentSnippets($pool_id,
            $title_filter,
            $format_filter,
            $keyword_filter,
            $caption_filter);

         return \ilUtil::sortArray(array_merge($mobs, $snippets), "title", "asc");
    }
}
