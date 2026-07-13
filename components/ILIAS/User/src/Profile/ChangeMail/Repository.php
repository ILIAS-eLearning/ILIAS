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

namespace ILIAS\User\Profile\ChangeMail;

interface Repository
{
    public function getNewTokenForUser(\ilObjUser $user, string $new_email, int $now): Token;
    public function hasUserValidEmailConfirmationToken(\ilObjUser $user): bool;

    /**
     * This Function will check if the token is actually valid for the given user
     * before returning the new email.
     */
    public function getTokenForTokenString(string $token_string, \ilObjUser $user): ?Token;
    public function moveToNextStep(Token $token, int $now): Token;
    public function deleteEntryByToken(string $token): void;
    public function deleteExpiredEntries(): void;
}
