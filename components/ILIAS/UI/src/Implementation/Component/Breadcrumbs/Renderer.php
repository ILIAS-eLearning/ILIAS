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

namespace ILIAS\UI\Implementation\Component\Breadcrumbs;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Component\Breadcrumbs\Breadcrumbs) {
            $this->cannotHandleComponent($component);
        }

        $tpl = $this->getTemplate("tpl.breadcrumbs.html", true, true);

        $tpl->setVariable("ARIA_LABEL", $this->txt('breadcrumbs_aria_label'));
        foreach ($component->getItems() as $key => $crumb) {
            $tpl->setCurrentBlock("crumbs");
            $tpl->setVariable("CRUMB", $default_renderer->render($crumb));
            if (!preg_match('/[a-zA-Z0-9\ ]$/', $crumb->getLabel())
                && ($key === array_key_last($component->getItems()))) {
                $tpl->setCurrentBlock("lrmmark");
                $tpl->setVariable("LRMMARK", htmlspecialchars_decode("&lrm;", ENT_HTML5));
            }
            $tpl->parseCurrentBlock();
        }
        return $tpl->get();
    }
}
