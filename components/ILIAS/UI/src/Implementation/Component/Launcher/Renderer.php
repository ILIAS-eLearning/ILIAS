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

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof Inline) {
            return $this->renderInline($component, $default_renderer);
        }
        return null;
    }

    public function renderInline(Inline $component, RendererInterface $default_renderer): string
    {
        if ($result = $component->getResult()) {
            $f = $component->getEvaluation();
            $f($result, $component);
        }

        $tpl = $this->getTemplate("tpl.launcher_inline.html", true, true);
        $ui_factory = $this->getUIFactory();

        $target = $component->getTarget()->getURL();
        $label = $component->getButtonLabel();
        $launchable = $component->isLaunchable();

        $launch_glyph = $ui_factory->symbol()->glyph()->launch();
        $start_button = $ui_factory->button()->bulky($launch_glyph, $label, (string) $target);

        if ($modal = $component->getModal()) {
            if ($modal_submit_lable = $component->getModalSubmitLabel()) {
                $modal = $modal->withSubmitLabel($modal_submit_lable);
            }

            if ($modal_cancel_label = $component->getModalCancelLabel()) {
                $modal = $modal->withCancelButtonLabel($modal_cancel_label);
            }

            $tpl->setVariable("FORM", $default_renderer->render($modal));
            $start_button = $start_button->withOnClick($modal->getShowSignal());
        }

        if (!$launchable) {
            $start_button = $start_button->withUnavailableAction();
        }
        if ($status_icon = $component->getStatusIcon()) {
            $tpl->setVariable("STATUS_ICON", $default_renderer->render($status_icon));
        }
        if ($status_message = $component->getStatusMessageBox()) {
            $tpl->setVariable("STATUS_MESSAGE", $default_renderer->render($status_message));
        }
        $tpl->setVariable("DESCRIPTION", $component->getDescription());
        $tpl->setVariable("BUTTON", $default_renderer->render($start_button));

        return $tpl->get();
    }
}
