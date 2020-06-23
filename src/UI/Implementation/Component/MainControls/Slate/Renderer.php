<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

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
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);
        if ($component instanceof ISlate\Notification) {
            return $this->renderNotificationSlate($component, $default_renderer);
        }
        if ($component instanceof ISlate\Combined) {
            $contents = $this->getCombinedSlateContents($component);
        } else {
            $contents = $component->getContents();
        }
        return $this->renderSlate($component, $contents, $default_renderer);
    }

    protected function getCombinedSlateContents(
        ISlate\Slate $component
    ) {
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
                        ->withAdditionalOnloadCode(
                            function ($id) use ($mb_id, $trigger_signal) {
                                return "
                                    il.UI.maincontrols.mainbar.addTriggerSignal('{$trigger_signal}');
                                    il.UI.maincontrols.mainbar.addPartIdAndEntry('{$mb_id}', 'triggerer', '{$id}');
                                ";
                            }
                        );
                }
                $contents[] = $triggerer;
            }
            $contents[] = $entry;
        }
        return $contents;
    }

    protected function renderSlate(
        ISlate\Slate $component,
        $contents,
        RendererInterface $default_renderer
    ) {
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
            function ($id) use ($slate_signals, $mb_id) {
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
    ) {
        $contents = [];
        foreach ($component->getContents() as $entry) {
            $contents[] = $entry;
        }
        $tpl = $this->getTemplate("Slate/tpl.notification.html", true, true);
        $tpl->setVariable('NAME', $component->getName());
        $tpl->setVariable('CONTENTS', $default_renderer->render($contents));
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/MainControls/slate.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            ISlate\Legacy::class,
            ISlate\Combined::class,
            ISlate\Notification::class
        );
    }
}
