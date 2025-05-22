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

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\UI\Component\Input\Field\PhpUploadLimit;
use ILIAS\FileServices\FileServicesLegacyInitialisationAdapter;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;
use ILIAS\Setup\Agent;
use ILIAS\Refinery\Factory;

class FileServices implements Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $implement[PhpUploadLimit::class] = static fn(): FileServicesLegacyInitialisationAdapter =>
            new FileServicesLegacyInitialisationAdapter();
        $implement[GlobalUploadLimit::class] = static fn(): FileServicesLegacyInitialisationAdapter =>
            new FileServicesLegacyInitialisationAdapter();

        $contribute[Agent::class] = static fn(): \ilFileServicesSetupAgent =>
            new \ilFileServicesSetupAgent(
                $pull[Factory::class]
            );
    }
}
