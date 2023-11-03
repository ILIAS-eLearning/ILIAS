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

namespace ILIAS\Services\WOPI\Handler;

use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class GetFileInfoResponse extends Response
{
    public function __construct(
        Revision $revision,
        int $current_user_id,
    ) {
        $URI = new URI(ILIAS_HTTP_PATH);
        $origin = $URI->getSchema() . '://' . $URI->getHost();
        $lookup_name = \ilObjUser::_lookupName($current_user_id);

        parent::__construct([
            self::BASE_FILE_NAME => $revision->getTitle(),
            self::VERSION => $revision->getVersionNumber(),
            self::OWNER_ID => $revision->getOwnerId(),
            self::USER_ID => $current_user_id,
            self::SIZE => $revision->getInformation()->getSize(),
            self::SUPPORTS_CONTAINERS => false,
            self::SUPPORTS_DELETE_FILE => false,
            self::SUPPORTS_UPDATE => true,
            self::READ_ONLY => false,
            self::RESTRICTED_WEB_VIEW_ONLY => true,
            self::USER_CAN_WRITE => true,
            self::USER_FRIENDLY_NAME => $lookup_name['firstname'] . ' ' . $lookup_name['lastname'],
            self::USER_CAN_ATTEND => false,
            self::LAST_MODIFIED_TIME => $revision->getInformation()->getCreationDate()->format(DATE_ATOM),
            self::USER_CAN_NOT_WRITE_RELATIVE => true,
            self::POST_MESSAGE_ORIGIN => $origin,
            self::CLOSE_BUTTON_CLOSES_WINDOW => false,
            self::CLOSE_URL => '#',
            self::EDIT_MODE_POST_MESSAGE => true,
            self::EDIT_NOTIFICATION_POST_MESSAGE => true,
            self::CLOSE_POST_MESSAGE => true,
            self::SUPPORTS_LOCKS => false,
            self::SUPPORTS_GET_LOCK => false,
            self::USER_CAN_RENAME => false
        ]);
    }
}
