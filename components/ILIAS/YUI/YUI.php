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

class YUI implements Component\Component
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
        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/yahoo-dom-event/yahoo-dom-event.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/connection/connection-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/dragdrop/dragdrop-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/element/element-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/container/container-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/utilities/utilities.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/resize/resize-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/container/container_core-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/menu/menu-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/button/button-min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/cookie/cookie.js");

        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/container/assets/skins/sam/container.css");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/button/assets/skins/sam/button.css");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("yui2/build/menu/assets/skins/sam/menu.css");
        */
    }
}
