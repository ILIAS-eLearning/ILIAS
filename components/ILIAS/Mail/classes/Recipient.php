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

namespace ILIAS\Mail;

use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result;
use ILIAS\LegalDocuments\Conductor;
use ilObjUser;
use ilMailOptions;

final class Recipient
{
    public function __construct(
        private readonly int $user_id,
        private readonly ?ilObjUser $user,
        private readonly ilMailOptions $mail_options,
        private readonly Conductor $legal_documents
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getMailOptions(): ilMailOptions
    {
        return $this->mail_options;
    }

    public function isUser(): bool
    {
        return !is_null($this->user);
    }

    public function isUserActive(): bool
    {
        return $this->user->getActive();
    }

    public function evaluateInternalMailReadability(): Result
    {
        if (!$this->user->checkTimeLimit()) {
            return new Error('Account expired.');
        }

        return $this->legal_documents->userCanReadInternalMail()->applyTo(new Ok($this->user));
    }

    public function userWantsToReceiveExternalMails(): bool
    {
        return $this->mail_options->getIncomingType() === ilMailOptions::INCOMING_EMAIL ||
            $this->mail_options->getIncomingType() === ilMailOptions::INCOMING_BOTH;
    }

    public function onlyToExternalMailAddress(): bool
    {
        return $this->mail_options->getIncomingType() === ilMailOptions::INCOMING_EMAIL;
    }

    /**
     * @return string[]
     */
    public function getExternalMailAddress(): array
    {
        return $this->mail_options->getExternalEmailAddresses();
    }
}
