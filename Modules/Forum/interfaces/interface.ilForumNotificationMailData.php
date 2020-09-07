<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilForumNotificationMailData
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
interface ilForumNotificationMailData
{
    /**
     * @return int
     */
    public function getRefId();

    /**
     * @return int
     */
    public function getObjId();

    /**
     * @return int frm_data.top_pk
     */
    public function getForumId();
    
    /**
     * @return string frm_data.top_name
     */
    public function getForumTitle();

    /**
     * @return int
     */
    public function getThreadId();
    
    /**
     * @return string frm_threads.thr_subject
     */
    public function getThreadTitle();

    /**
     * @return int
     */
    public function getPostId();

    /**
     * @return string frm_posts.pos_subject
     */
    public function getPostTitle();

    /**
     * @return string frm_posts.pos_message
     */
    public function getPostMessage();
    
    /**
     * @return string frm_posts.pos_author_id
     */
    public function getPosAuthorId();

    /**
     * @return int
     */
    public function getPostUpdateUserId();
    
    /**
     * @return string frm_posts.pos_display_user_id
     */
    public function getPosDisplayUserId();

    /**
     * @return string frm_posts.pos_usr_alias
     */
    public function getPosUserAlias();

    /**
     * @param \ilLanguage $user_lang
     * @return string
     */
    public function getPostUserName(\ilLanguage $user_lang);

    /**
     * @return string frm_posts.pos_date
     */
    public function getPostDate();

    /**
     * @return string frm_posts.pos_update
     */
    public function getPostUpdate();

    /**
     * @param \ilLanguage $user_lang
     * @return string
     */
    public function getPostUpdateUserName(\ilLanguage $user_lang);
    
    /**
     * @return string frm_posts.pos_cens
     */
    public function getPostCensored();

    /**
     * @return string frm_posts.pos_cens_date
     */
    public function getPostCensoredDate();

    /**
     * @return string
     */
    public function getCensorshipComment();
    
    /**
     * @return array file names
     */
    public function getAttachments();

    /**
     * @return string
     */
    public function getDeletedBy();
}
