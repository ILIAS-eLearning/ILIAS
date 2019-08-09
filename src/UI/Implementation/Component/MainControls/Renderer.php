<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts.and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\Slate\Slate;
use ILIAS\UI\Implementation\Render\Template as UITemplateWrapper;

class Renderer extends AbstractComponentRenderer
{
    const BLOCK_MAINBAR_ENTRIES = 'trigger_item';
    const BLOCK_MAINBAR_TOOLS = 'tool_trigger_item';
    const BLOCK_METABAR_ENTRIES = 'meta_element';

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof MainBar) {
            return $this->renderMainbar($component, $default_renderer);
        }
        if ($component instanceof MetaBar) {
            return $this->renderMetabar($component, $default_renderer);
        }
    }

    protected function renderMainbar(MainBar $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.mainbar.html", true, true);
        $active =  $component->getActive();
        $tools = $component->getToolEntries();
        $signals = [
            'entry' => $component->getEntryClickSignal(),
            'tools' => $component->getToolsClickSignal(),
            'close_slates' => $component->getDisengageAllSignal(),
            'tools_removal' => $component->getToolsRemovalSignal()
        ];


        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_MAINBAR_ENTRIES,
            $component->getEntries(),
            $active
        );

        if (count($tools) > 0) {
            $tools_button = $component->getToolsButton();
            $this->addTools($tpl, $default_renderer, $tools_button, $tools, $signals, $active);
        }

        $more_button = $component->getMoreButton();
        $this->addMoreSlate($tpl, $default_renderer, static::BLOCK_MAINBAR_ENTRIES, $more_button, $signals, $active);
        $this->addCloseSlateButton($tpl, $default_renderer, $signals);
        $this->addMainbarJS($tpl, $component, $signals, $active);

        return $tpl->get();
    }

    protected function renderMetabar(MetaBar $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.metabar.html", true, true);
        $active = '';
        $signals = [
            'entry' => $component->getEntryClickSignal(),
            'close_slates' => $component->getDisengageAllSignal()
        ];

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_METABAR_ENTRIES,
            $component->getEntries(),
            $active
        );

        $more_button = $this->getUIFactory()->button()->bulky(
            $this->getUIFactory()->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/options-vertical.svg', ''),
            'more',
            '#'
        );

        $this->addMoreSlate($tpl, $default_renderer, static::BLOCK_METABAR_ENTRIES, $more_button, $signals, $active);

        $component = $component->withOnLoadCode(
            function ($id) use ($signals) {
                $entry_signal = $signals['entry'];
                $close_slates_signal = $signals['close_slates'];
                return "
					il.UI.maincontrols.metabar.registerSignals(
						'{$id}',
						'{$entry_signal}',
						'{$close_slates_signal}',
					);
					il.UI.maincontrols.metabar.init();
					$(window).resize(il.UI.maincontrols.metabar.init);
				";
            }
        );
        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
        return $tpl->get();
    }


    protected function renderTriggerButtonsAndSlates(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        Signal $entry_signal,
        string $block,
        array $entries,
        string $active = null
    ) {
        foreach ($entries as $id=>$entry) {
            $engaged = (string) $id === $active;

            if ($entry instanceof Slate) {
                $f = $this->getUIFactory();
                $secondary_signal = $entry->getToggleSignal();
                if ($block === static::BLOCK_MAINBAR_TOOLS) {
                    $secondary_signal = $entry->getShowSignal();
                }
                $button = $f->button()->bulky($entry->getSymbol(), $entry->getName(), '#')
                    ->withOnClick($entry_signal)
                    ->appendOnClick($secondary_signal)
                    ->withEngagedState($engaged);

                $slate = $entry;
                $slate = $slate->withEngaged(false); //init disengaged, onLoadCode will "click" the button
            } else {
                $button = $entry;
                $slate = null;
            }

            $tpl->setCurrentBlock($block);
            $tpl->setVariable("BUTTON", $default_renderer->render($button));
            $tpl->parseCurrentBlock();

            if ($slate) {
                $tpl->setCurrentBlock("slate_item");
                $tpl->setVariable("SLATE", $default_renderer->render($slate));
                $tpl->parseCurrentBlock();
            }
        }
    }

    protected function addCloseSlateButton(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        array $signals
    ) {
        $f = $this->getUIFactory();
        $btn_disengage = $f->button()->bulky($f->symbol()->glyph()->back("#"), "close", "#")
            ->withOnClick($signals['close_slates']);
        $tpl->setVariable("CLOSE_SLATES", $default_renderer->render($btn_disengage));
    }

    protected function addTools(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        Component\Button\Bulky $tools_button,
        array $tools,
        array $signals,
        string $active = null
    ) {
        $f = $this->getUIFactory();

        $btn_tools = $tools_button
            ->withOnClick($signals['tools'])
            ->withEngagedState(false); //if a tool-entry is active, onLoadCode will "click" the button

        $btn_removetool = $f->button()->close()
            ->withOnClick($signals['tools_removal']);

        $tpl->setCurrentBlock("tools_trigger");
        $tpl->setVariable("BUTTON", $default_renderer->render($btn_tools));
        $tpl->parseCurrentBlock();

        $tpl->setCurrentBlock("tool_removal");
        $tpl->setVariable("REMOVE_TOOL", $default_renderer->render($btn_removetool));
        $tpl->parseCurrentBlock();

        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            static::BLOCK_MAINBAR_TOOLS,
            $tools,
            $active
        );
    }

    protected function addMoreSlate(
        UITemplateWrapper $tpl,
        RendererInterface $default_renderer,
        string $block,
        Component\Button\Bulky $more_button,
        array $signals,
        string $active = null
    ) {
        $f = $this->getUIFactory();
        $more_label = $more_button->getLabel();
        $more_symbol = $more_button->getIconOrGlyph();
        $more_slate = $f->maincontrols()->slate()->combined($more_label, $more_symbol, $f->legacy(''));
        $this->renderTriggerButtonsAndSlates(
            $tpl,
            $default_renderer,
            $signals['entry'],
            $block,
            [$more_slate],
            $active
        );
    }

    protected function addMainbarJS(
        UITemplateWrapper $tpl,
        MainBar $component,
        array $signals,
        string $active = null
    ) {
        $component = $component->withOnLoadCode(
            function ($id) use ($signals) {
                $entry_signal = $signals['entry'];
                $tools_signal = $signals['tools'];
                $close_slates_signal = $signals['close_slates'];
                $tool_removal_signal = $signals['tools_removal'];
                return "
					il.UI.maincontrols.mainbar.registerSignals(
						'{$id}',
						'{$entry_signal}',
						'{$close_slates_signal}',
						'{$tools_signal}',
						'{$tool_removal_signal}'
					);
					il.UI.maincontrols.mainbar.initMore();
					$(window).resize(il.UI.maincontrols.mainbar.initMore);
				";
            }
        );

        if ($active) {
            $component = $component->withAdditionalOnLoadCode(
                function ($id) {
                    return "il.UI.maincontrols.mainbar.initActive('{$id}');";
                }
            );
        }

        $id = $this->bindJavaScript($component);
        $tpl->setVariable('ID', $id);
    }


    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/MainControls/mainbar.js');
        $registry->register('./src/UI/templates/js/MainControls/metabar.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            MetaBar::class,
            MainBar::class
        );
    }
}
