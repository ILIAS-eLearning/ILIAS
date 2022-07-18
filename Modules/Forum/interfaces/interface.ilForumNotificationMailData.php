<?php declare(strict_types=1);

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
 * Interface ilForumNotificationMailData
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
interface ilForumNotificationMailData
{
    public function getRefId() : int;

    public function getObjId() : int;

    public function getForumId() : int;

    /** @return ilObjCourse|ilObjGroup|null */
    public function closestContainer() : ?ilObject;

    public function providesClosestContainer() : bool;

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
