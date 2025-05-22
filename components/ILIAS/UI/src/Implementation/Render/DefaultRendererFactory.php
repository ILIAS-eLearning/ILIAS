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
use ILIAS\UI\Implementation\FactoryInternal;
use ILIAS\UI\HelpTextRetriever;
use ILIAS\Language\Language;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

class DefaultRendererFactory implements RendererFactory
{
    public function __construct(
        protected FactoryInternal $ui_factory,
        protected TemplateFactory $tpl_factory,
        protected Language $lng,
        protected JavaScriptBinding $js_binding,
        protected ImagePathResolver $image_path_resolver,
        protected DataFactory $data_factory,
        protected HelpTextRetriever $help_text_retriever,
        protected UploadLimitResolver $upload_limit_resolver,
    ) {
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
            $this->image_path_resolver,
            $this->data_factory,
            $this->help_text_retriever,
            $this->upload_limit_resolver,
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
}
