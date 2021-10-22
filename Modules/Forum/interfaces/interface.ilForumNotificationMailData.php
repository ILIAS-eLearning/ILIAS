<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilForumNotificationMailData
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
interface ilForumNotificationMailData
{
    public function getRefId() : int;

    public function getObjId() : int;

    public function getForumId() : int;

    public function getForumTitle() : string;

    public function getThreadId() : int;

    public function getThreadTitle() : string;

    public function getPostId() : int;

    public function getPostTitle() : string;

    public function getPostMessage() : ?string;

    public function getPosAuthorId() : int;

    public function getPostUpdateUserId() : int;

    public function getPosDisplayUserId() : int;

    public function getPosUserAlias() : string;

    public function getPostUserName(ilLanguage $user_lang) : string;

    public function getPostDate() : string;

    public function getPostUpdate() : string;

    public function getPostUpdateUserName(ilLanguage $user_lang) : string;

    public function isPostCensored() : bool;

    public function getPostCensoredDate() : string;

    public function getCensorshipComment() : string;

    public function getAttachments() : array;

    public function getDeletedBy() : string;
}
