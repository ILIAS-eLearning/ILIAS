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

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
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
}
