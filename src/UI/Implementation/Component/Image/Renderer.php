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
use ILIAS\UI\Implementation\Render\Template;
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
        if ($component->getSourceSet() !== null) {
            $tpl = $this->addSourceSetToTemplate($tpl, $component);
        }
        $tpl->setVariable("ALT", htmlspecialchars($component->getAlt()));
        $tpl->parseCurrentBlock();

        if (!empty($component->getAction())) {
            $tpl->touchBlock("action_end");
        }

        return $tpl->get();
    }

    protected function addSourceSetToTemplate(Template $tpl, Component\Component $component): Template
    {
        $source_set_array = $component->getSourceSet();
        $sizes = $component->getSizesSelectorStatement();
        $source_set_string = '';
        foreach ($source_set_array as $condition_descriptor => $source) {
            if (substr($condition_descriptor, -1) === true && $sizes === null) {
                return $tpl;
            }
            $source_set_string .= "{$source} {$condition_descriptor}, ";
        }

        $srcset_attributes_string = 'srcset="' . substr($source_set_string, 0, -2) . '" ';

        if ($sizes !== null) {
            $srcset_attributes_string .= 'sizes="' . $sizes . '" ';
        }

        $tpl->setVariable('SRC_SET_ATTRIBUTES', $srcset_attributes_string);
        return $tpl;
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName(): array
    {
        return [Component\Image\Image::class];
    }
}
