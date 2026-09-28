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

namespace ILIAS\WOPI\Handler;

use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class GetFileInfoResponse extends Response
{
    /**
     * $editable describes this session - the file was opened through an edit action and
     * may be written back. $user_can_write describes the user - they hold the permission
     * to edit this file, no matter which action they launched just now. Keeping the two
     * apart is what this response is about:
     *
     * - ReadOnly and SupportsUpdate follow the session, so a viewer stays a viewer.
     * - UserCanWrite follows the permission. WOPI clients explain a read-only session with
     *   it: OnlyOffice, for one, opens its viewer with "forcedViewMode" and tells the user
     *   the file is locked or their rights do not allow editing, which is plainly wrong for
     *   someone who may edit the file and simply opened the content tab.
     *
     * Two properties are deliberately not part of the response:
     *
     * - RestrictedWebViewOnly asks the client to restrict what the user may do with the
     *   file, and what that means is up to the client. It describes a restriction on the
     *   file, not the fact that this session opened a viewer.
     * - UserCanAttend is about the permission to view a broadcast of the file and has
     *   nothing to do with editing.
     *
     * Both used to be derived from $editable, which only says which WOPI action was
     * launched. See https://mantis.ilias.de/view.php?id=48246
     */
    public function __construct(
        Revision $revision,
        int $current_user_id,
        bool $editable = false,
        ?bool $user_can_write = null
    ) {
        $user_can_write ??= $editable;
        $URI = new URI(ILIAS_HTTP_PATH);
        $origin = $URI->getSchema() . '://' . $URI->getHost();
        $lookup_name = \ilObjUser::_lookupName($current_user_id);

        $title = preg_replace('/\.[^.]*$/', '', $revision->getTitle())
            . '.' . $revision->getInformation()->getSuffix();

        parent::__construct([
            self::BASE_FILE_NAME => $title,
            self::VERSION => $revision->getVersionNumber(),
            self::OWNER_ID => $revision->getOwnerId(),
            self::USER_ID => $current_user_id,
            self::SIZE => $revision->getInformation()->getSize(),
            self::SUPPORTS_CONTAINERS => false,
            self::SUPPORTS_DELETE_FILE => false,
            self::SUPPORTS_UPDATE => $editable,
            self::READ_ONLY => !$editable,
            self::USER_CAN_WRITE => $user_can_write,
            self::USER_FRIENDLY_NAME => $lookup_name['firstname'] . ' ' . $lookup_name['lastname'],
            self::LAST_MODIFIED_TIME => $revision->getInformation()->getCreationDate()->format(DATE_ATOM),
            self::USER_CAN_NOT_WRITE_RELATIVE => true,
            self::POST_MESSAGE_ORIGIN => $origin,
            self::CLOSE_BUTTON_CLOSES_WINDOW => false,
            self::CLOSE_URL => '#',
            // ILIAS has no handler for the UI_Edit message, so it must not announce one:
            // clients offer a way into edit mode when EditModePostMessage is true, and
            // that button would do nothing here.
            self::EDIT_MODE_POST_MESSAGE => false,
            self::EDIT_NOTIFICATION_POST_MESSAGE => true,
            self::CLOSE_POST_MESSAGE => true,
            self::SUPPORTS_LOCKS => false,
            self::SUPPORTS_GET_LOCK => false,
            self::USER_CAN_RENAME => false
        ]);
    }
}
