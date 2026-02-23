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

namespace ILIAS\Notifications\Interfaces;

use ilLanguage;

/**
 * @internal This class should not be inherited! This is only to verify a push notification is working and not for practical use!
 */
final class InternalPushProvider implements PushProviderInterface
{
    final public function getIdentifier(): string
    {
        return '';
    }

    final public function getName(ilLanguage $lng): string
    {
        return '';
    }

    final public function getDescription(ilLanguage $lng): string
    {
        return '';
    }
}
