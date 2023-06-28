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

class Recipient
{
    protected int $user_id;
    protected ?ilObjUser $user;
    protected ilMailOptions $mail_options;

    public function __construct(int $user_id, ?ilObjUser $user, ilMailOptions $mail_options)
    {
        $this->user_id = $user_id;
        $this->user = $user;
        $this->mail_options = $mail_options;
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
        return ! is_null($this->user);
    }

    public function isUserActive(): bool
    {
        return $this->user->getActive();
    }

    public function isUserAbleToReadInternalMails(): bool
    {
        return !$this->user->hasToAcceptTermsOfService() && $this->user->checkTimeLimit();
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

    public function hasToAcceptTermsOfService(): bool
    {
        return $this->user->hasToAcceptTermsOfService();
    }

    public function checkTimeLimit(): bool
    {
        return $this->user->checkTimeLimit();
    }
}
