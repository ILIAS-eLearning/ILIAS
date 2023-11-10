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

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    public const DEFAULT_ICON_NAME = 'default';
    public const ICON_NAME_PATTERN = 'icon_%s.svg';

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var Component\Symbol\Icon\Icon $component
         */
        $this->checkComponent($component);
        $tpl = $this->getTemplate("tpl.icon.html", true, true);

        $id = $this->bindJavaScript($component);

        if ($id !== null) {
            $tpl->setCurrentBlock("with_id");
            $tpl->setVariable("ID", $id);
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable("NAME", $component->getName());
        $tpl->setVariable("SIZE", $component->getSize());

        $tpl = $this->renderLabel($component, $tpl);

        if ($component instanceof Component\Symbol\Icon\Standard) {
            $imagepath = $this->getStandardIconPath($component);
        } else {
            $imagepath = $component->getIconPath();
        }

        $ab = $component->getAbbreviation();
        if ($ab) {
            $tpl->setVariable("ABBREVIATION", $ab);

            $abbreviation_tpl = $this->getTemplate("tpl.abbreviation.svg", true, true);
            $abbreviation_tpl->setVariable("ABBREVIATION", $ab);
            $abbreviation = $abbreviation_tpl->get() . '</svg>';

            $image = file_get_contents("./public/" . $imagepath);
            $image = substr($image, strpos($image, '<svg '));
            $image = trim(str_replace('</svg>', $abbreviation, $image));
            $imagepath = "data:image/svg+xml;base64," . base64_encode($image);
        }

        $tpl->setVariable("CUSTOMIMAGE", $imagepath);

        if ($component->isDisabled()) {
            $tpl->touchBlock('disabled');
            $tpl->touchBlock('aria_disabled');
        }

        return $tpl->get();
    }

    protected function renderLabel(Component\Component $component, Template $tpl): Template
    {
        $tpl->setVariable('LABEL', $component->getLabel());
        return $tpl;
    }

    protected function getStandardIconPath(Component\Symbol\Icon\Icon $icon): string
    {
        $name = $icon->getName();
        $is_in_standard_icon_list = in_array($name, $icon->getAllStandardHandles());
        $is_in_page_editor_icon_list = in_array($name, $icon->getAllStandardPageEditorHandles());
        if (!$is_in_standard_icon_list && !$is_in_page_editor_icon_list) {
            $name = self::DEFAULT_ICON_NAME;
        }
        $pattern = self::ICON_NAME_PATTERN;

        $icon_path_name = '';
        $icon_name = sprintf($pattern, $name);
        if ($is_in_page_editor_icon_list) {
            $icon_path_name = 'page_editor/' . $icon_name;
        } else {
            $icon_path_name = 'standard/' . $icon_name;
        }

        return $this->getImagePathResolver()->resolveImagePath($icon_path_name);
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(Component\Symbol\Icon\Icon::class);
    }
}
