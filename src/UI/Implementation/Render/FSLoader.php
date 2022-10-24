<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Input\Field\Input;

/**
 * Loads renderers for components from the file system.
 *
 * To introduce a component that may react on the context of the rendering, you need to:
 *
 * * create a new implementation of RendererFactory in the implementation folder of that
 *   component
 * * introduce it as a dependency of this loader (and load at ilInitialisation::initUIFramework)
 * * make a special case for the components the new factory may create renderers for in
 *   FSLoader::getRendererFactoryFor
 */
class FSLoader implements Loader
{
    use LoaderHelper;

    private RendererFactory $default_renderer_factory;
    private RendererFactory $glyph_renderer_factory;
    private RendererFactory $icon_renderer_factory;
    private RendererFactory $field_renderer_factory;

    public function __construct(
        RendererFactory $default_renderer_factory,
        RendererFactory $glyph_renderer_factory,
        RendererFactory $icon_renderer_factory,
        RendererFactory $field_renderer_factory
    ) {
        $this->default_renderer_factory = $default_renderer_factory;
        $this->glyph_renderer_factory = $glyph_renderer_factory;
        $this->icon_renderer_factory = $icon_renderer_factory;
        $this->field_renderer_factory = $field_renderer_factory;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFor(Component $component, array $contexts): ComponentRenderer
    {
        $context_names = $this->getContextNames($contexts);
        $factory = $this->getRendererFactoryFor($component);
        return $factory->getRendererInContext($component, $context_names);
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component): RendererFactory
    {
        if ($component instanceof Glyph) {
            return $this->glyph_renderer_factory;
        }
        if ($component instanceof Icon) {
            return $this->icon_renderer_factory;
        }
        if ($component instanceof Input) {
            return $this->field_renderer_factory;
        }
        return $this->default_renderer_factory;
    }
}
