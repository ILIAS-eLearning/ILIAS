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

namespace ILIAS\Data;

/**
 * URLBuilderToken
 *
 * This is a token used by Data/URLBuilder to control usage of
 * URL parameters. See URLBuilder interface for more details.
 */

interface URLBuilderTokenInterface
{
    /**
     * Get the token hash value
     */
    public function getToken(): string;

    /**
     * Get the full name of the token
     * including its namespace
     */
    public function getName(): string;

    /**
     * Output the JS equivalent of the token
     * as a string. Used by the URLBuilder renderer.
     */
    public function render(): string;
}
