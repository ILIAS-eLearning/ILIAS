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

namespace ILIAS\Mail\Attachments;

interface MailAttachmentCommands
{
    public const CMD_SHOW_ATTACHMENTS = 'showAttachments';
    public const CMD_CANCEL_SAVE_ATTACHMENTS = 'cancelSaveAttachments';
    public const CMD_DELETE_ATTACHMENTS = 'deleteAttachments';
    public const CMD_HANDLE_TABLE_ACTIONS = 'handleTableActions';
    public const DEFAULT_CMD = self::CMD_SHOW_ATTACHMENTS;
    public const TABLE_CONFIRM_DELETE_ATTACHMENTS = 'confirmDeleteAttachments';
    public const TABLE_ACTION_SAVE_ATTACHMENTS = 'saveAttachments';
}
