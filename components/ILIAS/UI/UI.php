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

use ILIAS\GlobalScreen\Services;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;

class UI implements Component\Component
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
        $define[] = UI\Factory::class;
        $define[] = UI\Renderer::class;
        $define[] = UI\HelpTextRetriever::class;
        $define[] = UI\Component\Table\Storage::class;
        $define[] = UI\Component\Input\Field\PhpUploadLimit::class;
        $define[] = UI\Component\Input\Field\GlobalUploadLimit::class;
        $define[] = UI\Implementation\Render\ImagePathResolver::class;

        $implement[UI\Factory::class] = static fn() =>
            $internal[UI\Implementation\Factory::class];
        $implement[UI\Renderer::class] = static fn() =>
            $internal[UI\Implementation\DefaultRenderer::class];

        // =================================================================================
        // ATTENTION: these factories are only populated inside $provide in order to
        // keep plugin renderer- and factory-exvhanges possible. These factories will
        // only be internal again, once this functionality is improved for ILIAS 11.
        $provide[UI\Implementation\Component\Counter\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Counter\Factory::class];
        $provide[UI\Implementation\Component\Button\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Button\Factory::class];
        $provide[UI\Implementation\Component\Listing\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Listing\Factory::class];
        $provide[UI\Implementation\Component\Image\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Image\Factory::class];
        $provide[UI\Implementation\Component\Panel\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Panel\Factory::class];
        $provide[UI\Implementation\Component\Modal\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Modal\Factory::class];
        $provide[UI\Implementation\Component\Dropzone\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Dropzone\Factory::class];
        $provide[UI\Implementation\Component\Popover\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Popover\Factory::class];
        $provide[UI\Implementation\Component\Divider\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Divider\Factory::class];
        $provide[UI\Implementation\Component\Link\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Link\Factory::class];
        $provide[UI\Implementation\Component\Dropdown\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Dropdown\Factory::class];
        $provide[UI\Implementation\Component\Item\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Item\Factory::class];
        $provide[UI\Implementation\Component\ViewControl\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\ViewControl\Factory::class];
        $provide[UI\Implementation\Component\Chart\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Chart\Factory::class];
        $provide[UI\Implementation\Component\Input\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\Factory::class];
        $provide[UI\Implementation\Component\Table\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Table\Factory::class];
        $provide[UI\Implementation\Component\MessageBox\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\MessageBox\Factory::class];
        $provide[UI\Implementation\Component\Card\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Card\Factory::class];
        $provide[UI\Implementation\Component\Layout\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Layout\Factory::class];
        $provide[UI\Implementation\Component\MainControls\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\MainControls\Factory::class];
        $provide[UI\Implementation\Component\Tree\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Tree\Factory::class];
        $provide[UI\Implementation\Component\Menu\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Menu\Factory::class];
        $provide[UI\Implementation\Component\Symbol\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Symbol\Factory::class];
        $provide[UI\Implementation\Component\Toast\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Toast\Factory::class];
        $provide[UI\Implementation\Component\Legacy\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Legacy\Factory::class];
        $provide[UI\Implementation\Component\Launcher\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Launcher\Factory::class];
        $provide[UI\Implementation\Component\Entity\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Entity\Factory::class];
        $provide[UI\Implementation\Component\Panel\Listing\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Panel\Listing\Factory::class];
        $provide[UI\Implementation\Component\Modal\InterruptiveItem\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Modal\InterruptiveItem\Factory::class];
        $provide[UI\Implementation\Component\Chart\ProgressMeter\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Chart\ProgressMeter\Factory::class];
        $provide[UI\Implementation\Component\Chart\Bar\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Chart\Bar\Factory::class];
        $provide[UI\Implementation\Component\Input\ViewControl\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\ViewControl\Factory::class];
        $provide[UI\Implementation\Component\Input\Container\ViewControl\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\Container\ViewControl\Factory::class];
        $provide[UI\Implementation\Component\Table\Column\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Table\Column\Factory::class];
        $provide[UI\Implementation\Component\Table\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Table\Factory::class];
        $provide[UI\Implementation\Component\MainControls\Slate\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\MainControls\Slate\Factory::class];
        $provide[UI\Implementation\Component\Symbol\Icon\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Symbol\Icon\Factory::class];
        $provide[UI\Implementation\Component\Symbol\Glyph\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Symbol\Glyph\Factory::class];
        $provide[UI\Implementation\Component\Symbol\Avatar\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Symbol\Avatar\Factory::class];
        $provide[UI\Implementation\Component\Input\Container\Form\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\Container\Form\Factory::class];
        $provide[UI\Implementation\Component\Input\Container\Filter\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\Container\Filter\Factory::class];
        $provide[UI\Implementation\Component\Input\Field\Factory::class] = static fn() =>
            $internal[UI\Implementation\Component\Input\Field\Factory::class];
        // =================================================================================

        $internal[UI\Implementation\Factory::class] = static fn() =>
            new UI\Implementation\Factory(
                $internal[UI\Implementation\Component\Counter\Factory::class],
                $internal[UI\Implementation\Component\Button\Factory::class],
                $internal[UI\Implementation\Component\Listing\Factory::class],
                $internal[UI\Implementation\Component\Image\Factory::class],
                $internal[UI\Implementation\Component\Panel\Factory::class],
                $internal[UI\Implementation\Component\Modal\Factory::class],
                $internal[UI\Implementation\Component\Dropzone\Factory::class],
                $internal[UI\Implementation\Component\Popover\Factory::class],
                $internal[UI\Implementation\Component\Divider\Factory::class],
                $internal[UI\Implementation\Component\Link\Factory::class],
                $internal[UI\Implementation\Component\Dropdown\Factory::class],
                $internal[UI\Implementation\Component\Item\Factory::class],
                $internal[UI\Implementation\Component\ViewControl\Factory::class],
                $internal[UI\Implementation\Component\Chart\Factory::class],
                $internal[UI\Implementation\Component\Input\Factory::class],
                $internal[UI\Implementation\Component\Table\Factory::class],
                $internal[UI\Implementation\Component\MessageBox\Factory::class],
                $internal[UI\Implementation\Component\Card\Factory::class],
                $internal[UI\Implementation\Component\Layout\Factory::class],
                $internal[UI\Implementation\Component\MainControls\Factory::class],
                $internal[UI\Implementation\Component\Tree\Factory::class],
                $internal[UI\Implementation\Component\Menu\Factory::class],
                $internal[UI\Implementation\Component\Symbol\Factory::class],
                $internal[UI\Implementation\Component\Toast\Factory::class],
                $internal[UI\Implementation\Component\Legacy\Factory::class],
                $internal[UI\Implementation\Component\Launcher\Factory::class],
                $internal[UI\Implementation\Component\Entity\Factory::class],
            );

        $internal[UI\Implementation\Component\Counter\Factory::class] = static fn() =>
            new UI\Implementation\Component\Counter\Factory();

        $internal[UI\Implementation\Component\Button\Factory::class] = static fn() =>
            new UI\Implementation\Component\Button\Factory();

        $internal[UI\Implementation\Component\Listing\Factory::class] = static fn() =>
            new UI\Implementation\Component\Listing\Factory();

        $internal[UI\Implementation\Component\Image\Factory::class] = static fn() =>
            new UI\Implementation\Component\Image\Factory();

        $internal[UI\Implementation\Component\Panel\Factory::class] = static fn() =>
            new UI\Implementation\Component\Panel\Factory(
                $internal[UI\Implementation\Component\Panel\Listing\Factory::class],
            );
        $internal[UI\Implementation\Component\Panel\Listing\Factory::class] = static fn() =>
            new UI\Implementation\Component\Panel\Listing\Factory();

        $internal[UI\Implementation\Component\Modal\Factory::class] = static fn() =>
            new UI\Implementation\Component\Modal\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Modal\InterruptiveItem\Factory::class],
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
            );
        $internal[UI\Implementation\Component\SignalGeneratorInterface::class] = static fn() =>
            new UI\Implementation\Component\SignalGenerator();
        $internal[UI\Implementation\Component\Modal\InterruptiveItem\Factory::class] = static fn() =>
            new UI\Implementation\Component\Modal\InterruptiveItem\Factory();

        $internal[UI\Implementation\Component\Dropzone\Factory::class] = static fn() =>
            new UI\Implementation\Component\Dropzone\Factory(
                $internal[UI\Implementation\Component\Dropzone\File\Factory::class],
            );
        $internal[UI\Implementation\Component\Dropzone\File\Factory::class] = static fn() =>
            new UI\Implementation\Component\Dropzone\File\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
            );

        $internal[UI\Implementation\Component\Popover\Factory::class] = static fn() =>
            new UI\Implementation\Component\Popover\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
            );

        $internal[UI\Implementation\Component\Divider\Factory::class] = static fn() =>
            new UI\Implementation\Component\Divider\Factory();

        $internal[UI\Implementation\Component\Link\Factory::class] = static fn() =>
            new UI\Implementation\Component\Link\Factory();

        $internal[UI\Implementation\Component\Dropdown\Factory::class] = static fn() =>
            new UI\Implementation\Component\Dropdown\Factory();

        $internal[UI\Implementation\Component\Item\Factory::class] = static fn() =>
            new UI\Implementation\Component\Item\Factory();

        $internal[UI\Implementation\Component\ViewControl\Factory::class] = static fn() =>
            new UI\Implementation\Component\ViewControl\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
            );

        $internal[UI\Implementation\Component\Chart\Factory::class] = static fn() =>
            new UI\Implementation\Component\Chart\Factory(
                $internal[UI\Implementation\Component\Chart\ProgressMeter\Factory::class],
                $internal[UI\Implementation\Component\Chart\Bar\Factory::class],
            );
        $internal[UI\Implementation\Component\Chart\ProgressMeter\Factory::class] = static fn() =>
            new UI\Implementation\Component\Chart\ProgressMeter\Factory();
        $internal[UI\Implementation\Component\Chart\Bar\Factory::class] = static fn() =>
            new UI\Implementation\Component\Chart\Bar\Factory();

        $internal[UI\Implementation\Component\Input\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
                $internal[UI\Implementation\Component\Input\Container\Factory::class],
                $internal[UI\Implementation\Component\Input\ViewControl\Factory::class],
            );
        $internal[UI\Implementation\Component\Input\Field\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Field\Factory(
                $internal[UI\Implementation\Component\Input\UploadLimitResolver::class],
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $pull[Data\Factory::class],
                $pull[Refinery\Factory::class],
                $use[Language\Language::class]
            );
        $internal[UI\Implementation\Component\Input\UploadLimitResolver::class] = static fn() =>
            new UI\Implementation\Component\Input\UploadLimitResolver(
                $use[UI\Component\Input\Field\PhpUploadLimit::class],
                $use[UI\Component\Input\Field\GlobalUploadLimit::class],
            );
        $internal[UI\Implementation\Component\Input\Container\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Container\Factory(
                $internal[UI\Implementation\Component\Input\Container\Form\Factory::class],
                $internal[UI\Implementation\Component\Input\Container\Filter\Factory::class],
                $internal[UI\Implementation\Component\Input\Container\ViewControl\Factory::class],
            );
        $internal[UI\Implementation\Component\Input\Container\Form\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Container\Form\Factory(
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
            );
        $internal[UI\Implementation\Component\Input\Container\Filter\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Container\Filter\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
            );
        $internal[UI\Implementation\Component\Input\Container\ViewControl\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\Container\ViewControl\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Input\ViewControl\Factory::class],
            );
        $internal[UI\Implementation\Component\Input\ViewControl\Factory::class] = static fn() =>
            new UI\Implementation\Component\Input\ViewControl\Factory(
                $internal[UI\Implementation\Component\Input\Field\Factory::class],
                $pull[Data\Factory::class],
                $pull[Refinery\Factory::class],
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $use[Language\Language::class],
            );

        $internal[UI\Implementation\Component\Table\Factory::class] = static fn() =>
            new UI\Implementation\Component\Table\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Input\ViewControl\Factory::class],
                $internal[UI\Implementation\Component\Input\Container\ViewControl\Factory::class],
                $pull[Data\Factory::class],
                $internal[UI\Implementation\Component\Table\Column\Factory::class],
                $internal[UI\Implementation\Component\Table\Action\Factory::class],
                $use[UI\Component\Table\Storage::class],
                $internal[UI\Implementation\Component\Table\DataRowBuilder::class],
                $internal[UI\Implementation\Component\Table\OrderingRowBuilder::class],
            );
        $internal[UI\Implementation\Component\Table\Column\Factory::class] = static fn() =>
            new UI\Implementation\Component\Table\Column\Factory(
                $use[\ILIAS\Language\Language::class],
            );
        $internal[UI\Implementation\Component\Table\Action\Factory::class] = static fn() =>
            new UI\Implementation\Component\Table\Action\Factory();
        $internal[UI\Implementation\Component\Table\DataRowBuilder::class] = static fn() =>
            new UI\Implementation\Component\Table\DataRowBuilder();
        $internal[UI\Implementation\Component\Table\OrderingRowBuilder::class] = static fn() =>
            new UI\Implementation\Component\Table\OrderingRowBuilder();

        $internal[UI\Implementation\Component\MessageBox\Factory::class] = static fn() =>
            new UI\Implementation\Component\MessageBox\Factory();

        $internal[UI\Implementation\Component\Card\Factory::class] = static fn() =>
            new UI\Implementation\Component\Card\Factory();

        $internal[UI\Implementation\Component\Layout\Factory::class] = static fn() =>
            new UI\Implementation\Component\Layout\Factory();

        $internal[UI\Implementation\Component\MainControls\Factory::class] = static fn() =>
            new UI\Implementation\Component\MainControls\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\MainControls\Slate\Factory::class],
            );
        $internal[UI\Implementation\Component\MainControls\Slate\Factory::class] = static fn() =>
            new UI\Implementation\Component\MainControls\Slate\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
                $internal[UI\Implementation\Component\Counter\Factory::class],
                $internal[UI\Implementation\Component\Symbol\Factory::class],
            );

        $internal[UI\Implementation\Component\Tree\Factory::class] = static fn() =>
            new UI\Implementation\Component\Tree\Factory();

        $internal[UI\Implementation\Component\Menu\Factory::class] = static fn() =>
            new UI\Implementation\Component\Menu\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
            );

        $internal[UI\Implementation\Component\Symbol\Factory::class] = static fn() =>
            new UI\Implementation\Component\Symbol\Factory(
                $internal[UI\Implementation\Component\Symbol\Icon\Factory::class],
                $internal[UI\Implementation\Component\Symbol\Glyph\Factory::class],
                $internal[UI\Implementation\Component\Symbol\Avatar\Factory::class],
            );
        $internal[UI\Implementation\Component\Symbol\Icon\Factory::class] = static fn() =>
            new UI\Implementation\Component\Symbol\Icon\Factory();
        $internal[UI\Implementation\Component\Symbol\Glyph\Factory::class] = static fn() =>
            new UI\Implementation\Component\Symbol\Glyph\Factory();
        $internal[UI\Implementation\Component\Symbol\Avatar\Factory::class] = static fn() =>
            new UI\Implementation\Component\Symbol\Avatar\Factory();

        $internal[UI\Implementation\Component\Toast\Factory::class] = static fn() =>
            new UI\Implementation\Component\Toast\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
            );

        $internal[UI\Implementation\Component\Legacy\Factory::class] = static fn() =>
            new UI\Implementation\Component\Legacy\Factory(
                $internal[UI\Implementation\Component\SignalGeneratorInterface::class],
            );

        $internal[UI\Implementation\Component\Launcher\Factory::class] = static fn() =>
            new UI\Implementation\Component\Launcher\Factory(
                $internal[UI\Implementation\Component\Modal\Factory::class],
            );

        $internal[UI\Implementation\Component\Entity\Factory::class] = static fn() =>
            new UI\Implementation\Component\Entity\Factory();

        $internal[UI\Implementation\DefaultRenderer::class] = static fn() =>
            new UI\Implementation\DefaultRenderer(
                $internal[UI\Implementation\Render\Loader::class],
                $internal[UI\Implementation\Render\JavaScriptBinding::class],
                $use[Language\Language::class],
            );
        $internal[UI\Implementation\Render\Loader::class] = static fn() =>
            new UI\Implementation\Render\LoaderCachingWrapper(
                new UI\Implementation\Render\LoaderResourceRegistryWrapper(
                    $internal[UI\Implementation\Render\ResourceRegistry::class],
                    new UI\Implementation\Render\FSLoader(
                        new UI\Implementation\Render\DefaultRendererFactory(
                            $use[UI\Factory::class],
                            $internal[UI\Implementation\Render\TemplateFactory::class],
                            $use[Language\Language::class],
                            $internal[UI\Implementation\Render\JavaScriptBinding::class],
                            $use[UI\Implementation\Render\ImagePathResolver::class],
                            $pull[Data\Factory::class],
                            $use[UI\HelpTextRetriever::class],
                            $internal[UI\Implementation\Component\Input\UploadLimitResolver::class],
                        ),
                        new UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory(
                            $use[UI\Factory::class],
                            $internal[UI\Implementation\Render\TemplateFactory::class],
                            $use[Language\Language::class],
                            $internal[UI\Implementation\Render\JavaScriptBinding::class],
                            $use[UI\Implementation\Render\ImagePathResolver::class],
                            $pull[Data\Factory::class],
                            $use[UI\HelpTextRetriever::class],
                            $internal[UI\Implementation\Component\Input\UploadLimitResolver::class],
                        ),
                        new UI\Implementation\Component\Symbol\Icon\IconRendererFactory(
                            $use[UI\Factory::class],
                            $internal[UI\Implementation\Render\TemplateFactory::class],
                            $use[Language\Language::class],
                            $internal[UI\Implementation\Render\JavaScriptBinding::class],
                            $use[UI\Implementation\Render\ImagePathResolver::class],
                            $pull[Data\Factory::class],
                            $use[UI\HelpTextRetriever::class],
                            $internal[UI\Implementation\Component\Input\UploadLimitResolver::class],
                        ),
                        new UI\Implementation\Component\Input\Field\FieldRendererFactory(
                            $use[UI\Factory::class],
                            $internal[UI\Implementation\Render\TemplateFactory::class],
                            $use[Language\Language::class],
                            $internal[UI\Implementation\Render\JavaScriptBinding::class],
                            $use[UI\Implementation\Render\ImagePathResolver::class],
                            $pull[Data\Factory::class],
                            $use[UI\HelpTextRetriever::class],
                            $internal[UI\Implementation\Component\Input\UploadLimitResolver::class],
                        )
                    )
                )
            );
        $internal[UI\Implementation\Render\JavaScriptBinding::class] = static fn() =>
            new UI\Implementation\Render\ilJavaScriptBinding(
                $use[UICore\GlobalTemplate::class],
            );
        $internal[UI\Implementation\Render\ResourceRegistry::class] = static fn() =>
            new UI\Implementation\Render\ilResourceRegistry(
                $use[UICore\GlobalTemplate::class],
            );
        $internal[UI\Implementation\Render\TemplateFactory::class] = static fn() =>
            new UI\Implementation\Render\ilTemplateWrapperFactory();

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Button/button.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Chart/Bar/dist/bar.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Core/dist/core.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Counter/dist/counter.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Dropdown/dist/dropdown.js");

        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("dropzone/dist/min/dropzone.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Dropzone/File/dropzone.js");

        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Image/dist/image.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Container/dist/filter.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/dist/input.factory.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/dynamic_inputs_renderer.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/file.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/input.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Input/Field/tagInput.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Item/dist/notification.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/dist/mainbar.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/dist/maincontrols.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/MainControls/system_info.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Menu/dist/drilldown.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Modal/dist/modal.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Page/stdpage.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Popover/popover.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Table/dist/table.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Toast/toast.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/Tree/tree.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\ComponentJS($this, "js/ViewControl/dist/viewcontrols.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "images", "assets");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "fonts", "assets");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\OfComponent($this, "ui-examples", "assets");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("@yaireo/tagify/dist/tagify.min.js");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("@yaireo/tagify/dist/tagify.css");
        $contribute[Component\Resource\PublicAsset::class] = static fn() =>
            new Component\Resource\NodeModule("chart.js/dist/chart.umd.js");
        /*
        those are contributed by MediaObjects
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("mediaelement/build/mediaelement-and-player.min.js");
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("./node_modules/mediaelement/build/mediaelementplayer.min.css");
        */
        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("mediaelement/build/renderers/vimeo.min.js");
        */
        /* This library was missing after discussing dependencies for ILIAS 10
        $contribute[Component\Resource\PublicAsset::class] = fn() =>
            new Component\Resource\NodeModule("webui-popover/dist/jquery.webui-popover.min.js");
        */


        // This is included via anonymous classes as a testament to the fact, that
        // the templates-folder should probably be moved to some component.
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "templates/default/delos.css";
            }
            public function getTarget(): string
            {
                return "assets/css/delos.css";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "templates/default/delos_cont.css";
            }
            public function getTarget(): string
            {
                return "assets/css/delos_cont.css";
            }
        };
    }
}
