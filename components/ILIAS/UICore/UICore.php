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

class UICore implements Component\Component
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
        $define[] = UICore\GlobalTemplate::class;

        $implement[UICore\GlobalTemplate::class] = static fn() =>
            new UICore\GlobalTemplateLegacyInitialisationAdapter();

        $contribute[\ILIAS\Setup\Agent::class] = fn() =>
            new \ilUICoreSetupAgent();

        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("bootstrap/dist/js/bootstrap.min.js");
        */
    }
}
