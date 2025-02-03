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

/**
 * Class ilCtrlToken is responsible for generating and storing
 * unique CSRF tokens.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlToken implements ilCtrlTokenInterface
{
    /**
     * Holds a temporarily generated token.
     *
     * @var string
     */
    private string $token;

    /**
     * ilCtrlToken Constructor
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function verifyWith(string $token): bool
    {
        return ($this->token === $token);
    }

    /**
     * @inheritDoc
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
