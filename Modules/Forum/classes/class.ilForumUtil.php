<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumUtil
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumUtil
{
    /**
     * @param      $user_alias
     * @param bool $is_anonymized
     * @return string
     */
    public static function getPublicUserAlias($user_alias, $is_anonymized = false)
    {
        global $DIC;
        $ilUser = $DIC->user();
        $lng = $DIC->language();
        
        if ($is_anonymized) {
            if (!strlen($user_alias)) {
                $user_alias = $lng->txt('forums_anonymous');
            }
        
            return $user_alias;
        }
        return $ilUser->getLogin();
    }

    public static function collectPostInformationByPostId($post_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            '
		SELECT frm.top_name, thr.thr_subject, pos.pos_subject FROM frm_data frm
		INNER JOIN frm_threads thr ON frm.top_pk = thr.thr_top_fk
		INNER JOIN frm_posts pos ON thr.thr_pk = pos.pos_thr_fk
		WHERE pos_pk = %s',
            array('integer'),
            array($post_id)
        );
        
        $info = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $info['forum_title'] = $row['top_name'];
            $info['thread_title'] = $row['thr_subject'];
            $info['post_title'] = $row['pos_subject'];
        }
        return $info;
    }
    
    /**
     * @param string $post_message
     * @param string $source_type
     * @param int $source_id
     * @param string $target_type
     * @param int $target_id
     */
    public static function moveMediaObjects($post_message, $source_type, $source_id, $target_type, $target_id, $direction = 0)
    {
        $mediaObjects = ilRTE::_getMediaObjects($post_message, $direction);
        $myMediaObjects = ilObjMediaObject::_getMobsOfObject($source_type, $source_id);
        foreach ($mediaObjects as $mob) {
            foreach ($myMediaObjects as $myMob) {
                if ($mob == $myMob) {
                    // change usage
                    ilObjMediaObject::_removeUsage($mob, $source_type, $source_id);
                    break;
                }
            }
            ilObjMediaObject::_saveUsage($mob, $target_type, $target_id);
        }
    }
    
    /**
     * @param $post_message
     * @param $target_type
     * @param $target_id
     */
    public static function saveMediaObjects($post_message, $target_type, $target_id, $direction = 0)
    {
        $mediaObjects = ilRTE::_getMediaObjects($post_message, $direction);
        
        foreach ($mediaObjects as $mob) {
            ilObjMediaObject::_saveUsage($mob, $target_type, $target_id);
        }
    }
}
