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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Component\Input\Field\Node;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Menu;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Renders Drilldown Menu's in the Input\Field context, specifically used
 * by the Tree (Multi) Select Field.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class FieldContextRenderer extends Renderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Drilldown) {
            return $this->renderDrilldownMenu($component, $default_renderer);
        }
        $this->cannotHandleComponent($component);
    }

    protected function renderMenuItems(
        Menu\Menu $component,
        RendererInterface $default_renderer
    ): string {
        // delegate Input\Field\Node components directly to the rendering chain.
        return $default_renderer->render($component->getItems());
    }

    protected function addMenuFilter(
        Menu\Menu $component,
        Template $template,
        RendererInterface $default_renderer,
    ): void {
        // do not render filter; Tree(Multi)Select needs special search mechanism.
    }
}
