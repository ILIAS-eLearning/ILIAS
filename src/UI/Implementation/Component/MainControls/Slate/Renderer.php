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

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\MainControls\Slate as ISlate;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);
        switch (true) {
            case ($component instanceof ISlate\Notification):
                return $this->renderNotificationSlate($component, $default_renderer);
                break;

            case ($component instanceof ISlate\Combined):
            case ($component instanceof ISlate\Drilldown):
                $contents = $this->getCombinedSlateContents($component);
                break;

            default:
                $contents = $component->getContents();
        }

        return $this->renderSlate($component, $contents, $default_renderer);
    }

    protected function getCombinedSlateContents(
        ISlate\Slate $component
    ): array {
        $f = $this->getUIFactory();
        $contents = [];
        foreach ($component->getContents() as $entry) {
            if ($entry instanceof ISlate\Slate && !$entry instanceof ISlate\Notification) {
                $trigger_signal = $entry->getToggleSignal();
                $triggerer = $f->button()->bulky($entry->getSymbol(), $entry->getName(), '#')
                    ->withOnClick($trigger_signal);

                $mb_id = $entry->getMainBarTreePosition();
                if ($mb_id) {
                    $trigger_signal = $component->getTriggerSignal($mb_id);
                    $triggerer = $triggerer
                        ->withOnClick($trigger_signal)
                        ->withAdditionalOnLoadCode(
                            fn ($id) => "
                                    il.UI.maincontrols.mainbar.addTriggerSignal('{$trigger_signal}');
                                    il.UI.maincontrols.mainbar.addPartIdAndEntry('{$mb_id}', 'triggerer', '{$id}');
                                "
                        );
                }
                $contents[] = $triggerer;
            }

            if ($component instanceof ISlate\Drilldown) {
                $entry = $entry->withPersistenceId($component->getMainBarTreePosition());
            }
            $contents[] = $entry;
        }


        return $contents;
    }

    protected function renderSlate(
        ISlate\Slate $component,
        $contents,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("Slate/tpl.slate.html", true, true);

        $tpl->setVariable('CONTENTS', $default_renderer->render($contents));

        $aria_role = $component->getAriaRole();
        if ($aria_role != null) {
            $tpl->setCurrentBlock("with_aria_role");
            $tpl->setVariable("ARIA_ROLE", $aria_role);
            $tpl->parseCurrentBlock();
        }

        if ($component->getEngaged()) {
            $tpl->touchBlock('engaged');
        } else {
            $tpl->touchBlock('disengaged');
        }

        $slate_signals = [
            'toggle' => $component->getToggleSignal(),
            'engage' => $component->getEngageSignal(),
            'replace' => $component->getReplaceSignal()
        ];

        $mb_id = $component->getMainBarTreePosition();

        if ($mb_id) {
            $tpl->setVariable('TREE_DEPTH', $component->getMainBarTreeDepth());
        }

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($slate_signals, $mb_id): string {
                $js = "fn = il.UI.maincontrols.slate.onSignal;";
                foreach ($slate_signals as $key => $signal) {
                    $js .= "$(document).on('{$signal}', function(event, signalData) { fn('{$key}', event, signalData, '{$id}'); return false;});";
                }

                if ($mb_id) {
                    $js .= "il.UI.maincontrols.mainbar.addPartIdAndEntry('{$mb_id}', 'slate', '{$id}');";
                }


                return $js;
            }
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);

        return $tpl->get();
    }

    protected function renderNotificationSlate(
        ISlate\Slate $component,
        RendererInterface $default_renderer
    ): string {
        $contents = $component->getContents();
        $tpl = $this->getTemplate("Slate/tpl.notification.html", true, true);
        $tpl->setVariable('NAME', $component->getName());
        $tpl->setVariable('CONTENTS', $default_renderer->render($contents));
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/MainControls/slate.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            ISlate\Legacy::class,
            ISlate\Combined::class,
            ISlate\Notification::class,
            ISlate\Drilldown::class
        );
    }
}
