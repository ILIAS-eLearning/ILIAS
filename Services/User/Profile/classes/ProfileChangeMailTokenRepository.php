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

namespace ILIAS\User\Profile;

interface ProfileChangeMailTokenRepository
{
    public function getNewTokenForUser(\ilObjUser $user, string $new_email) : string;

    /**
     * This Function will check if the token is actually valid for the given user
     * before returning the new email.
     *
     * @return string The new email a user wishes to be used or an empty string
     * if validation failed or there is no usable entry.
     */
    public function getNewEmailForUser(\ilObjUser $user, string $token) : string;
    public function deleteEntryByToken(string $token) : void;
}
