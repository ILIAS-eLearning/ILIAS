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

class Init implements Component\Component
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
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "register.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "pwassist.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "login.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "logout.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "index.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "ilias.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "error.php");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\Endpoint($this, "sso/index.php", "sso");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, ".htaccess", ".");

        $contribute[Component\EntryPoint::class] = static fn() =>
            new Init\AllModernComponents(
                $pull[\ILIAS\Refinery\Factory::class],
                $pull[\ILIAS\Data\Factory::class],
                $use[\ILIAS\UI\Factory::class],
                $use[\ILIAS\UI\Renderer::class],
                $pull[\ILIAS\UI\Implementation\Component\Counter\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Button\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Listing\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Listing\Workflow\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Listing\Entity\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Image\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Player\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Panel\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Modal\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Dropzone\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Popover\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Divider\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Link\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Dropdown\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Item\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\ViewControl\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Chart\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Table\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\MessageBox\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Card\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Layout\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Layout\Page\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Layout\Alignment\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\MainControls\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Tree\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Tree\Node\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Menu\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Symbol\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Toast\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Legacy\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Launcher\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Entity\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Panel\Listing\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Panel\Secondary\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Chart\Bar\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\ViewControl\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\Container\ViewControl\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Table\Column\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Table\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\MainControls\Slate\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Symbol\Icon\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\Container\Form\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\Field\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Prompt\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Prompt\State\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Progress\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Progress\State\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Progress\State\Bar\Factory::class],
                $pull[\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class],
            );
    }
}
