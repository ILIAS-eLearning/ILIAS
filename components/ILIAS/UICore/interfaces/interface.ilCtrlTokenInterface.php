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

/**
 * Interface ilCtrlTokenInterface describes an ilCtrl token.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlTokenInterface
{
    /**
     * Compares the given token to the stored one of the given user.
     *
     * @param string $token
     * @return bool
     */
    public function verifyWith(string $token): bool;

    /**
     * Returns the token string of this instance.
     *
     * @return string
     */
    public function getToken(): string;
}
