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

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var Component\Image\Image $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.image.html", true, true);

        if (($sources = $component->getAdditionalHighResSources()) !== []) {
            $component = $component->withAdditionalOnLoadCode(
                fn(string $id): string => "
                    const imageElement = il.UI.image.getImageElement('$id');
                    if (imageElement === null) {
                        throw new Error(`Image '$id' not found.`);
                    }
                    il.UI.image.loadHighResolutionSource(
                        imageElement,
                        {$this->getHighResSourceMapForJs($sources)}
                    );
                "
            );
        }

        $id = $this->bindJavaScript($component);
        if (!empty($component->getAction())) {
            $tpl->touchBlock("action_begin");

            if (is_string($component->getAction())) {
                $tpl->setCurrentBlock("with_href");
                $tpl->setVariable("HREF", $component->getAction());
                $tpl->parseCurrentBlock();
            }

            if (is_array($component->getAction())) {
                $tpl->setCurrentBlock("with_href");
                $tpl->setVariable("HREF", "#");
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("with_id");
                $tpl->setVariable("ID", $id);
                $tpl->parseCurrentBlock();
            }
        }

        if (!is_array($component->getAction()) && $id !== null) {
            $tpl->setVariable("IMG_ID", " id='" . $id . "' ");
        }

        $tpl->setCurrentBlock($component->getType());
        $tpl->setVariable("SOURCE", $component->getSource());
        $tpl->setVariable("ALT", htmlspecialchars($component->getAlt()));
        $tpl->parseCurrentBlock();

        if (!empty($component->getAction())) {
            $tpl->touchBlock("action_end");
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName(): array
    {
        return [Component\Image\Image::class];
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/image.min.js');
    }

    /**
     * @param array<int, string> $resources
     */
    protected function getHighResSourceMapForJs(array $resources): string
    {
        $map_entries = '';
        foreach ($resources as $min_width => $source) {
            $map_entries .= "[$min_width, '$source'],\n";
        }

        return "new Map([\n$map_entries])";
    }
}
