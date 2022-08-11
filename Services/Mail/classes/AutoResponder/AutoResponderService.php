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

namespace ILIAS\Mail\AutoResponder;

use ilMailOptions;

interface AutoResponderService
{
    public function isAutoResponderEnabled() : bool;
    public function enableAutoResponder() : void;
    public function disableAutoResponder() : void;
    public function handleAutoResponderMails(int $auto_responder_receiver_usr_id) : void;
    public function enqueueAutoResponderIfEnabled(ilMailOptions $mail_recipient_mail_options) : void;
    public function emptyAutoResponderData() : void;
}
