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

interface PushProviderInterface
{
    /**
     * Return a unique identifier
     */
    public function getIdentifier(): string;

    /**
     * Return the presentation name of the provider.
     */
    public function getName(ilLanguage $lng): string;

    /**
     * Return the description of the provider.
     */
    public function getDescription(ilLanguage $lng): string;
}
