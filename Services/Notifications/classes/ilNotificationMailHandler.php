<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Notifications;

use ILIAS\Notifications\Model\ilNotificationObject;
use ilMail;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationMailHandler extends ilNotificationHandler
{
    public function notify(ilNotificationObject $notification): void
    {
        $sender_id = $notification->handlerParams['mail']['sender'] ?? ANONYMOUS_USER_ID;
        $mail = new ilMail((int) $sender_id);
        $mail->appendInstallationSignature(true);
        $mail->enqueue(
            $notification->user->getLogin(),
            '',
            '',
            $notification->title,
            $notification->longDescription,
            []
        );
    }
}
