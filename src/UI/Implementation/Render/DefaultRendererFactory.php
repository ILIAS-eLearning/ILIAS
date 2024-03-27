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

namespace ILIAS\UI\Implementation\Render;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as RootFactory;
use ilLanguage;

class DefaultRendererFactory implements RendererFactory
{
    protected RootFactory $ui_factory;
    protected TemplateFactory $tpl_factory;
    protected ilLanguage $lng;
    protected JavaScriptBinding $js_binding;
    protected Refinery $refinery;
    protected ImagePathResolver $image_path_resolver;
    protected DataFactory $data_factory;

    public function __construct(
        RootFactory $ui_factory,
        TemplateFactory $tpl_factory,
        ilLanguage $lng,
        JavaScriptBinding $js_binding,
        Refinery $refinery,
        ImagePathResolver $image_path_resolver,
        DataFactory $data_factory
    ) {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
        $this->refinery = $refinery;
        $this->image_path_resolver = $image_path_resolver;
        $this->data_factory = $data_factory;
    }

    /**
     * @inheritdocs
     */
    public function getRendererInContext(Component $component, array $contexts): ComponentRenderer
    {
        $name = $this->getRendererNameFor($component);
        return new $name(
            $this->ui_factory,
            $this->tpl_factory,
            $this->lng,
            $this->js_binding,
            $this->refinery,
            $this->image_path_resolver,
            $this->data_factory
        );
    }

    /**
     * Get the name for the renderer of Component class.
     */
    protected function getRendererNameFor(Component $component): string
    {
        $class = get_class($component);
        $parts = explode("\\", $class);
        $parts[count($parts) - 1] = "Renderer";
        return implode("\\", $parts);
    }

    /**
     * @inheritdocs
     */
    public function getJSBinding(): JavaScriptBinding
    {
        return $this->js_binding;
    }
}
