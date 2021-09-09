<?php declare(strict_types=1);
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
    public function getRefId() : int;

    /**
     * @return int
     */
    public function getObjId() : int;

    /**
     * @return int frm_data.top_pk
     */
    public function getForumId() : int;

    /**
     * @return string frm_data.top_name
     */
    public function getForumTitle() : string;

    /**
     * @return int
     */
    public function getThreadId() : int;

    /**
     * @return string frm_threads.thr_subject
     */
    public function getThreadTitle() : string;

    /**
     * @return int
     */
    public function getPostId() : int;

    /**
     * @return string frm_posts.pos_subject
     */
    public function getPostTitle() : string;

    /**
     * @return string frm_posts.pos_message
     */
    public function getPostMessage();

    /**
     * @return int frm_posts.pos_author_id
     */
    public function getPosAuthorId();

    /**
     * @return int
     */
    public function getPostUpdateUserId() : int;

    /**
     * @return int frm_posts.pos_display_user_id
     */
    public function getPosDisplayUserId() : int;

    /**
     * @return string frm_posts.pos_usr_alias
     */
    public function getPosUserAlias() : string;

    /**
     * @param ilLanguage $user_lang
     * @return string
     */
    public function getPostUserName(ilLanguage $user_lang) : string;

    /**
     * @return string frm_posts.pos_date
     */
    public function getPostDate() : string;

    /**
     * @return string|null frm_posts.pos_update
     */
    public function getPostUpdate() : string|null;

    /**
     * @param ilLanguage $user_lang
     * @return string|null
     */
    public function getPostUpdateUserName(ilLanguage $user_lang) : string|null;

    /**
     * @return string frm_posts.pos_cens
     */
    public function getPostCensored();

    /**
     * @return string|null frm_posts.pos_cens_date
     */
    public function getPostCensoredDate() : string|null;

    /**
     * @return string|null
     */
    public function getCensorshipComment() : string|null;

    /**
     * @return array file names
     */
    public function getAttachments() : array;

    /**
     * @return string|null
     */
    public function getDeletedBy() : string|null;
}
