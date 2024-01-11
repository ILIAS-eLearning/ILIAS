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

namespace ILIAS\UI\Implementation\Component\Symbol\Avatar;

use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;

class Renderer extends AbstractComponentRenderer
{
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof Component\Symbol\Avatar\Letter) {
            $tpl = $this->getTemplate('tpl.avatar_letter.html', true, true);
            $tpl->setVariable('ARIA_LABEL', $this->getLabel($component));
            $tpl->setVariable('MODE', 'letter');
            $tpl->setVariable('TEXT', $component->getAbbreviation());
            $tpl->setVariable('COLOR', (string) $component->getBackgroundColorVariant());
            return $tpl->get();
        }

        if ($component instanceof Component\Symbol\Avatar\Picture) {
            $tpl = $this->getTemplate('tpl.avatar_picture.html', true, true);
            $tpl->setVariable('ARIA_LABEL', $this->getLabel($component));
            $tpl->setVariable('MODE', 'picture');
            $tpl->setVariable('CUSTOMIMAGE', $component->getPicturePath());
            return $tpl->get();
        }

        return null;
    }

    protected function getLabel(Component\Symbol\Avatar\Avatar $component): string
    {
        $label = $component->getLabel();
        if ($label === "") {
            $label = $this->txt("user_avatar");
        }
        return $label;
    }
}
