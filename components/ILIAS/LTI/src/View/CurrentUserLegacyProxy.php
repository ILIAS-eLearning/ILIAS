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

namespace ILIAS\LTI\View;

/**
 * Asks the legacy environment on every call, so it can be built at build time and is never older than the
 * user of the request.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class CurrentUserLegacyProxy implements CurrentUser
{
    public function getAuthMode(): string
    {
        global $DIC;

        // without a user, as in the cron job or the SOAP server, and before ilInitialisation has set it up
        if (!\ilContext::hasUser() || !$DIC instanceof \ILIAS\DI\Container || !$DIC->offsetExists('ilUser')) {
            return '';
        }

        return (string) $DIC->user()->getAuthMode();
    }
}
