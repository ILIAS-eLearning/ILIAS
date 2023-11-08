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

declare(strict_types=1);

namespace ILIAS\components\WOPI\Handler;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Response implements \JsonSerializable
{
    protected const BASE_FILE_NAME = 'BaseFileName';
    protected const VERSION = 'Version';
    protected const OWNER_ID = 'OwnerId';
    protected const USER_ID = 'UserId';
    protected const SIZE = 'Size';
    protected const SUPPORTS_CONTAINERS = 'SupportsContainers';
    protected const SUPPORTS_DELETE_FILE = 'SupportsDeleteFile';
    protected const SUPPORTS_UPDATE = 'SupportsUpdate';
    protected const READ_ONLY = 'ReadOnly';
    protected const LAST_MODIFIED_TIME = 'LastModifiedTime';
    protected const USER_CAN_ATTEND = 'UserCanAttend';
    protected const USER_FRIENDLY_NAME = 'UserFriendlyName';
    protected const USER_CAN_WRITE = 'UserCanWrite';
    protected const RESTRICTED_WEB_VIEW_ONLY = 'RestrictedWebViewOnly';
    protected const USER_CAN_NOT_WRITE_RELATIVE = 'UserCanNotWriteRelative';
    protected const POST_MESSAGE_ORIGIN = 'PostMessageOrigin';
    protected const CLOSE_BUTTON_CLOSES_WINDOW = 'CloseButtonClosesWindow';
    protected const CLOSE_URL = 'CloseUrl';
    protected const EDIT_MODE_POST_MESSAGE = 'EditModePostMessage';
    protected const EDIT_NOTIFICATION_POST_MESSAGE = 'EditNotificationPostMessage';
    protected const CLOSE_POST_MESSAGE = 'ClosePostMessage';
    protected const SUPPORTS_LOCKS = 'SupportsLocks';
    protected const SUPPORTS_GET_LOCK = 'SupportsGetLock';
    protected const USER_CAN_RENAME = 'UserCanRename';

    public function __construct(
        private array $data
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
